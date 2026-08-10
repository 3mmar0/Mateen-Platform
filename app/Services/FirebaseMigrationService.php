<?php

namespace App\Services;

use App\Models\{
    Assignment,
    ContactMessage,
    Conversation,
    LearningMaterial,
    LibraryItem,
    Message,
    NewsItem,
    RegistrationRequest,
    ScheduleEntry,
    StudentProfile,
    Subject,
    User,
    UserDevice
};
use App\Services\FirebaseMigration\DocumentMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Firebase → MySQL import.
 *
 * Password reset for imported users:
 * - Mapped users get must_reset_password=true and a random password by default.
 * - Optional adminEmail/adminPassword preserves one account for immediate login.
 */
class FirebaseMigrationService
{
    public function __construct(private DocumentMapper $mapper) {}

    /**
     * @param  array{admin_email?:?string,admin_password?:?string}  $options
     */
    public function load(string $path, bool $dryRun = false, array $options = []): array
    {
        $raw = file_get_contents($path);
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $json = $this->normalizeExport($json);

        if ($dryRun) {
            return $this->auditFromArray($json);
        }

        $counts = [];
        $adminId = null;
        $userByFirebase = [];
        $subjectBySlug = [];
        $materialByLegacy = [];
        $adminEmail = strtolower(trim((string) ($options['admin_email'] ?? '')));
        $adminPassword = (string) ($options['admin_password'] ?? '');

        DB::transaction(function () use ($json, &$counts, &$adminId, &$userByFirebase, &$subjectBySlug, &$materialByLegacy, $adminEmail, $adminPassword) {
            $importedSubjectIds = [];
            foreach ($json['subjects'] ?? $json['staticSubjects'] ?? [] as $d) {
                $slug = $d['slug'] ?? $d['id'] ?? null;
                if (! $slug && empty($d['title']) && empty($d['name'])) {
                    continue;
                }
                $slug = $slug ?: Str::slug($d['title'] ?? $d['name'] ?? Str::random(8));
                $payload = $this->mapper->subject(array_merge($d, ['slug' => $slug]));
                $title = $payload['title'] ?? null;
                // Prefer slug match; fall back to Arabic title so demo stubs (tafsir) merge into Firebase (tafseer).
                $s = Subject::query()->where('slug', $slug)->first()
                    ?? ($title ? Subject::query()->where('title', $title)->first() : null);
                if ($s) {
                    $s->fill($payload)->save();
                } else {
                    $s = Subject::create($payload);
                }
                $importedSubjectIds[] = $s->id;
                $subjectBySlug[$s->slug] = $s->id;
                if (isset($d['id'])) {
                    $subjectBySlug[(string) $d['id']] = $s->id;
                }
                if (isset($d['name'])) {
                    $subjectBySlug[$d['name']] = $s->id;
                }
                if (isset($d['title'])) {
                    $subjectBySlug[$d['title']] = $s->id;
                }
                $counts['subjects'] = 1 + ($counts['subjects'] ?? 0);
            }
            if ($importedSubjectIds !== []) {
                $orphanIds = Subject::query()->whereNotIn('id', array_unique($importedSubjectIds))->pluck('id');
                if ($orphanIds->isNotEmpty()) {
                    Subject::query()->whereIn('id', $orphanIds)->delete();
                    $counts['subjects_removed'] = $orphanIds->count();
                }
            }

            foreach ($json['users'] ?? [] as $d) {
                if (empty($d['email'])) {
                    $counts['users_skipped'] = 1 + ($counts['users_skipped'] ?? 0);

                    continue;
                }
                $preserve = ($adminEmail !== '' && strtolower($d['email']) === $adminEmail && $adminPassword !== '')
                    ? $adminPassword
                    : null;
                $u = User::updateOrCreate(
                    ['email' => $d['email']],
                    $this->mapper->user($d, $preserve)
                );
                $fid = $d['uid'] ?? $d['id'] ?? null;
                if ($fid) {
                    $userByFirebase[(string) $fid] = $u->id;
                }
                if (($d['role'] ?? '') === 'admin' && ! $adminId) {
                    $adminId = $u->id;
                }
                $counts['users'] = 1 + ($counts['users'] ?? 0);
            }

            $adminId ??= User::query()->where('role', 'admin')->value('id')
                ?? User::query()->value('id')
                ?? 1;

            foreach ($json['students'] ?? [] as $d) {
                $uid = null;
                if (! empty($d['userId']) || ! empty($d['uid'])) {
                    $uid = $userByFirebase[(string) ($d['userId'] ?? $d['uid'])] ?? null;
                }
                if (! $uid && ! empty($d['email'])) {
                    $uid = User::query()->where('email', $d['email'])->value('id');
                }
                if (! $uid) {
                    $email = $d['email'] ?? ('imported.student.'.($d['id'] ?? Str::lower(Str::random(8))).'@mateen.import');
                    $u = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $d['name'] ?? 'طالبة',
                            'password' => Hash::make(str()->password(32)),
                            'role' => 'student',
                            'must_reset_password' => true,
                            'firebase_uid' => $d['id'] ?? null,
                        ]
                    );
                    $uid = $u->id;
                    if (isset($d['id'])) {
                        $userByFirebase[(string) $d['id']] = $uid;
                    }
                }
                StudentProfile::updateOrCreate(
                    ['user_id' => $uid],
                    $this->mapper->studentProfile($d, $uid)
                );
                $counts['students'] = 1 + ($counts['students'] ?? 0);
            }

            $hasMaterials = ! empty($json['materials']) || ! empty($json['learningMaterials']);
            if ($hasMaterials) {
                // Avoid duplicating materials on each sync (import always creates rows).
                Assignment::query()->update(['learning_material_id' => null]);
                LearningMaterial::query()->delete();
            }
            foreach ($json['materials'] ?? $json['learningMaterials'] ?? [] as $d) {
                $sid = $this->resolveSubjectId($d, $subjectBySlug);
                if (! $sid) {
                    continue;
                }
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                $m = LearningMaterial::create($this->mapper->material($d, $sid, $creator));
                if (isset($d['id'])) {
                    $materialByLegacy[(string) $d['id']] = $m->id;
                }
                $counts['materials'] = 1 + ($counts['materials'] ?? 0);
            }

            foreach ($json['assignments'] ?? [] as $d) {
                $sid = $this->resolveSubjectId($d, $subjectBySlug);
                if (! $sid) {
                    continue;
                }
                $mid = isset($d['materialId']) ? ($materialByLegacy[(string) $d['materialId']] ?? null) : null;
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                Assignment::create($this->mapper->assignment($d, $sid, $creator, $mid));
                $counts['assignments'] = 1 + ($counts['assignments'] ?? 0);
            }

            foreach ($json['libraryItems'] ?? $json['library'] ?? [] as $d) {
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                LibraryItem::create($this->mapper->library($d, $creator));
                $counts['libraryItems'] = 1 + ($counts['libraryItems'] ?? 0);
            }

            // Replace demo seed news/schedules when importing real content
            if (! empty($json['news']) || ! empty($json['newsItems'])) {
                NewsItem::query()->delete();
            }
            foreach ($json['news'] ?? $json['newsItems'] ?? [] as $d) {
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                NewsItem::create($this->mapper->news($d, $creator));
                $counts['news'] = 1 + ($counts['news'] ?? 0);
            }

            $hasSchedules = ! empty($json['schedules']) || ! empty($json['scheduleEntries']) || ! empty($json['events']);
            if ($hasSchedules) {
                ScheduleEntry::query()->delete();
            }
            foreach ($json['schedules'] ?? $json['scheduleEntries'] ?? [] as $d) {
                $sid = $this->resolveSubjectId($d, $subjectBySlug);
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                ScheduleEntry::create($this->mapper->schedule($d, $sid, $creator));
                $counts['schedules'] = 1 + ($counts['schedules'] ?? 0);
            }
            foreach ($json['events'] ?? [] as $d) {
                $sid = $this->resolveSubjectId($d, $subjectBySlug);
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                ScheduleEntry::create($this->mapper->eventAsSchedule($d, $sid, $creator));
                $counts['events'] = 1 + ($counts['events'] ?? 0);
            }

            foreach ($json['conversations'] ?? [] as $d) {
                $c = Conversation::create([]);
                $participantIds = [];
                foreach ($d['participants'] ?? $d['memberIds'] ?? [] as $pid) {
                    $uid = $userByFirebase[(string) $pid] ?? null;
                    if ($uid) {
                        $participantIds[] = $uid;
                    }
                }
                if ($participantIds) {
                    $c->participants()->sync(array_unique($participantIds));
                }
                foreach ($d['messages'] ?? [] as $msg) {
                    $sender = isset($msg['senderId']) ? ($userByFirebase[(string) $msg['senderId']] ?? null) : null;
                    Message::create($this->mapper->message($msg, $c->id, $sender));
                    $counts['messages'] = 1 + ($counts['messages'] ?? 0);
                }
                $counts['conversations'] = 1 + ($counts['conversations'] ?? 0);
            }

            foreach ($json['messages'] ?? [] as $d) {
                if (empty($d['conversationId'])) {
                    continue;
                }
                $counts['messages_skipped'] = 1 + ($counts['messages_skipped'] ?? 0);
            }

            foreach ($json['contactMessages'] ?? $json['contacts'] ?? $json['contact_messages'] ?? [] as $d) {
                ContactMessage::create($this->mapper->contact($d));
                $counts['contactMessages'] = 1 + ($counts['contactMessages'] ?? 0);
            }

            foreach ($json['registrationRequests'] ?? $json['registrations'] ?? $json['registration_requests'] ?? [] as $d) {
                RegistrationRequest::create($this->mapper->registration($d));
                $counts['registrationRequests'] = 1 + ($counts['registrationRequests'] ?? 0);
            }

            foreach ($json['tokens'] ?? $json['fcmTokens'] ?? $json['devices'] ?? [] as $d) {
                $uid = $userByFirebase[(string) ($d['userId'] ?? $d['uid'] ?? '')] ?? null;
                $token = $d['token'] ?? $d['fcm_token'] ?? $d['fcmToken'] ?? null;
                if (! $uid || ! $token) {
                    continue;
                }
                UserDevice::updateOrCreate(
                    ['fcm_token' => $token],
                    $this->mapper->device($d, $uid)
                );
                $counts['tokens'] = 1 + ($counts['tokens'] ?? 0);
            }
        });

        return $counts;
    }

    public function audit(string $path): array
    {
        $raw = file_get_contents($path);
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return $this->auditFromArray($this->normalizeExport($json));
    }

    private function auditFromArray(array $json): array
    {
        $keys = [
            'users', 'students', 'subjects', 'staticSubjects', 'materials', 'learningMaterials',
            'libraryItems', 'library', 'assignments', 'conversations', 'messages',
            'news', 'newsItems', 'events', 'schedules', 'scheduleEntries', 'tokens', 'fcmTokens', 'devices',
            'contactMessages', 'contacts', 'contact_messages', 'registrationRequests', 'registrations', 'registration_requests',
        ];
        $out = [];
        foreach ($keys as $k) {
            if (isset($json[$k]) && is_array($json[$k])) {
                $out[$k] = count($json[$k]);
            }
        }
        $msgCount = 0;
        foreach ($json['conversations'] ?? [] as $c) {
            $msgCount += count($c['messages'] ?? []);
        }
        if ($msgCount) {
            $out['nested_messages'] = $msgCount;
        }

        return $out;
    }

    private function normalizeExport(array $json): array
    {
        $byId = [];
        foreach ($json['conversations'] ?? [] as $c) {
            if (! isset($c['id'])) {
                continue;
            }
            $c['messages'] = $c['messages'] ?? [];
            $byId[(string) $c['id']] = $c;
        }
        $extra = $json['conversationMessages'] ?? [];
        if (is_array($extra)) {
            foreach ($extra as $cid => $msgs) {
                $cid = (string) $cid;
                if (! isset($byId[$cid])) {
                    $byId[$cid] = ['id' => $cid, 'participants' => [], 'messages' => []];
                }
                foreach ($msgs as $m) {
                    $byId[$cid]['messages'][] = $m;
                }
            }
        }
        if ($byId) {
            $json['conversations'] = array_values($byId);
        }

        return $json;
    }

    private function resolveSubjectId(array $d, array $map): ?int
    {
        foreach (['subject_id', 'subjectId', 'course', 'subject', 'slug'] as $k) {
            if (! empty($d[$k]) && isset($map[(string) $d[$k]])) {
                return $map[(string) $d[$k]];
            }
        }

        return null;
    }
}
