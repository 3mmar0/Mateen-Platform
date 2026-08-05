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
 * - All mapped users get must_reset_password=true and a random unusable password.
 * - After cutover, users use POST /auth/password/forgot then /auth/password/reset
 *   (or admin-assisted reset). First successful reset clears must_reset_password.
 */
class FirebaseMigrationService
{
    public function __construct(private DocumentMapper $mapper) {}

    public function load(string $path, bool $dryRun = false): array
    {
        $json = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if ($dryRun) {
            return $this->audit($path);
        }

        $counts = [];
        $adminId = null;
        $userByFirebase = [];
        $subjectBySlug = [];
        $materialByLegacy = [];

        DB::transaction(function () use ($json, &$counts, &$adminId, &$userByFirebase, &$subjectBySlug, &$materialByLegacy) {
            foreach ($json['subjects'] ?? $json['staticSubjects'] ?? [] as $d) {
                $s = Subject::updateOrCreate(
                    ['slug' => $d['slug'] ?? $d['id']],
                    $this->mapper->subject($d)
                );
                $subjectBySlug[$s->slug] = $s->id;
                if (isset($d['id'])) {
                    $subjectBySlug[(string) $d['id']] = $s->id;
                }
                if (isset($d['name'])) {
                    $subjectBySlug[$d['name']] = $s->id;
                }
                $counts['subjects'] = 1 + ($counts['subjects'] ?? 0);
            }

            foreach ($json['users'] ?? [] as $d) {
                $u = User::updateOrCreate(
                    ['email' => $d['email']],
                    $this->mapper->user($d)
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

            foreach ($json['news'] ?? $json['newsItems'] ?? [] as $d) {
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                NewsItem::create($this->mapper->news($d, $creator));
                $counts['news'] = 1 + ($counts['news'] ?? 0);
            }

            foreach ($json['schedules'] ?? $json['scheduleEntries'] ?? [] as $d) {
                $sid = $this->resolveSubjectId($d, $subjectBySlug);
                $creator = $userByFirebase[(string) ($d['createdBy'] ?? '')] ?? $adminId;
                ScheduleEntry::create($this->mapper->schedule($d, $sid, $creator));
                $counts['schedules'] = 1 + ($counts['schedules'] ?? 0);
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
                // Flat messages require prior conversation rows keyed by legacy id — skip if unmatched.
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
        $json = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $keys = [
            'users', 'students', 'subjects', 'staticSubjects', 'materials', 'learningMaterials',
            'libraryItems', 'library', 'assignments', 'conversations', 'messages',
            'news', 'newsItems', 'schedules', 'scheduleEntries', 'tokens', 'fcmTokens', 'devices',
            'contactMessages', 'contacts', 'contact_messages', 'registrationRequests', 'registrations', 'registration_requests',
        ];
        $out = [];
        foreach ($keys as $k) {
            if (isset($json[$k]) && is_array($json[$k])) {
                $out[$k] = count($json[$k]);
            }
        }

        return $out;
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
