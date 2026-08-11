<?php

namespace App\Services\FirebaseMigration;

use Illuminate\Support\Str;

class DocumentMapper
{
    public function subject(array $d): array
    {
        $iconData = $d['icon_data'] ?? $d['iconData'] ?? null;
        $iconUrl = $d['icon_url'] ?? $d['iconUrl'] ?? null;
        // Firebase often stores the Cloudinary URL in iconData (not base64).
        if (! $iconUrl && is_string($iconData) && str_starts_with($iconData, 'http')) {
            $iconUrl = $iconData;
        }

        return [
            'slug' => $d['slug'] ?? $d['id'],
            'title' => $d['title'] ?? $d['name'] ?? $d['id'],
            'icon' => $d['icon'] ?? null,
            'icon_url' => $iconUrl,
            'icon_data' => $iconData,
            'color' => $d['color'] ?? null,
            'meetings' => $d['meetings'] ?? null,
            'weeks' => $d['weeks'] ?? null,
            'level' => $d['level'] ?? $d['subtitle'] ?? null,
            'subtitle' => $d['subtitle'] ?? $d['level'] ?? null,
            'description' => $d['description'] ?? $d['desc'] ?? null,
            'axes' => $d['axes'] ?? $d['topics'] ?? null,
            'sort_order' => $d['sort_order'] ?? $d['addedAt'] ?? 0,
        ];
    }

    public function material(array $d, int $subjectId, int $createdBy): array
    {
        return [
            'subject_id' => $subjectId,
            'title' => $d['title'] ?? $d['name'] ?? 'مادة',
            'type' => $this->materialType($d),
            'body' => $d['body'] ?? $d['content'] ?? $d['description'] ?? $d['notes'] ?? null,
            'url' => $d['url'] ?? $d['link'] ?? null,
            'created_by' => $createdBy,
            'sort_order' => $d['sort_order'] ?? $d['order'] ?? $d['lectureNumber'] ?? 0,
        ];
    }

    private function materialType(array $d): string
    {
        $raw = strtolower(trim((string) ($d['type'] ?? '')));
        $url = strtolower((string) ($d['url'] ?? $d['link'] ?? ''));
        $map = [
            'pdf' => 'pdf',
            'video' => 'video',
            'article' => 'article',
            'link' => 'link',
            'محاضرة' => 'article',
            'ملخص' => 'article',
            'مرجع' => 'link',
            'فيديو' => 'video',
            'كتاب' => 'pdf',
        ];
        if (isset($map[$raw])) {
            return $map[$raw];
        }
        if (isset($map[(string) ($d['type'] ?? '')])) {
            return $map[(string) $d['type']];
        }
        if (str_contains($url, '.pdf')) {
            return 'pdf';
        }
        if (str_contains($url, 'youtube') || str_contains($url, 'youtu.be') || str_contains($url, 'vimeo')) {
            return 'video';
        }
        if ($url !== '') {
            return 'link';
        }

        return 'article';
    }

    public function studentProfile(array $d, int $userId): array
    {
        $interviewRaw = $d['interview_status'] ?? $d['interviewStatus'] ?? $d['interview'] ?? 'not_done';
        $interview = match (true) {
            in_array($interviewRaw, ['done', 'تم', true, 1, '1'], true) => 'done',
            in_array($interviewRaw, ['not_done', 'pending', 'لم تتم', false, 0, '0'], true) => 'not_done',
            default => 'not_done',
        };

        $statusRaw = $d['status_class'] ?? $d['statusClass'] ?? $d['status'] ?? 'newcomer';
        $statusClass = match (true) {
            in_array($statusRaw, ['mateen_girls', 'mateen', 'بنات متين'], true) => 'mateen_girls',
            in_array($statusRaw, ['newcomer', 'new', 'مستجد', 'المستجدات'], true) => 'newcomer',
            default => 'newcomer',
        };

        $accepted = $d['accepted'] ?? 'na';
        if (! in_array($accepted, ['accepted', 'rejected', 'na'], true)) {
            $accepted = match (true) {
                in_array($accepted, ['مقبولة', 'قبول', true, 1, '1'], true) => 'accepted',
                in_array($accepted, ['غير مقبولة', 'رفض', false, 0, '0'], true) => 'rejected',
                default => 'na',
            };
        }

        return [
            'user_id' => $userId,
            'interview_status' => $interview,
            'status_class' => $statusClass,
            'notes' => $d['notes'] ?? null,
            'extra' => array_filter([
                'legacy_id' => $d['id'] ?? null,
                'name' => $d['name'] ?? null,
                'order' => $d['order'] ?? null,
                'day' => $d['day'] ?? null,
                'dateH' => $d['dateH'] ?? null,
                'dateG' => $d['dateG'] ?? null,
                'hour' => $d['hour'] ?? null,
                'minute' => $d['minute'] ?? null,
                'ampm' => $d['ampm'] ?? null,
                'accepted' => $accepted,
                'archived' => (bool) ($d['archived'] ?? false),
                'archivedAt' => $this->ts($d['archivedAt'] ?? $d['archived_at'] ?? null),
                'uid' => $d['uid'] ?? $d['userId'] ?? null,
                'status' => in_array($statusRaw, ['mateen', 'new'], true) ? $statusRaw
                    : ($statusClass === 'mateen_girls' ? 'mateen' : 'new'),
                'placementScore' => $d['placementScore'] ?? $d['placement_score'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''),
        ];
    }

    public function assignment(array $d, int $subjectId, int $createdBy, ?int $materialId): array
    {
        return [
            'subject_id' => $subjectId,
            'learning_material_id' => $materialId,
            'title' => $d['title'] ?? 'واجب',
            'description' => $d['description'] ?? null,
            'due_at' => $this->ts($d['deadline'] ?? $d['due_at'] ?? null),
            'status' => $d['status'] ?? 'open',
            'created_by' => $createdBy,
        ];
    }

    public function conversationMeta(array $d): array
    {
        return ['legacy_id' => $d['id'] ?? null];
    }

    public function message(array $d, int $conversationId, ?int $senderId): array
    {
        return [
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'sender_display' => $d['senderName'] ?? $d['sender_display'] ?? null,
            'body' => $d['body'] ?? $d['text'] ?? $d['message'] ?? null,
            'media_url' => $d['media_url'] ?? $d['mediaUrl'] ?? $d['fileUrl'] ?? null,
            'media_type' => $d['media_type'] ?? $d['mediaType'] ?? null,
        ];
    }

    public function device(array $d, int $userId): array
    {
        return [
            'user_id' => $userId,
            'fcm_token' => $d['token'] ?? $d['fcm_token'] ?? $d['fcmToken'],
            'platform' => $d['platform'] ?? null,
            'last_seen_at' => $this->ts($d['last_seen_at'] ?? $d['updatedAt'] ?? null),
        ];
    }

    public function library(array $d, int $createdBy): array
    {
        return [
            'section' => $d['section'] ?? 'general',
            'title' => $d['title'] ?? 'عنصر',
            'description' => $d['description'] ?? null,
            'media_url' => $d['media_url'] ?? $d['url'] ?? null,
            'subject_filter' => $d['subject_filter'] ?? $d['course'] ?? null,
            'created_by' => $createdBy,
            'sort_order' => $d['sort_order'] ?? 0,
        ];
    }

    public function news(array $d, int $createdBy): array
    {
        $status = $d['status'] ?? 'published';
        if (! in_array($status, ['draft', 'published'], true)) {
            $status = 'published';
        }

        return [
            'title' => $d['title'] ?? 'خبر',
            'body' => $d['body'] ?? $d['content'] ?? '',
            'status' => $status,
            'published_at' => $this->ts($d['published_at'] ?? $d['createdAt'] ?? null) ?? now(),
            'created_by' => $createdBy,
        ];
    }

    public function schedule(array $d, ?int $subjectId, int $createdBy): array
    {
        $title = $d['title'] ?? $d['label'] ?? $d['name'] ?? 'موعد';
        $rawDate = $d['starts_at'] ?? $d['start'] ?? $d['date'] ?? $d['when'] ?? null;
        $parsedStarts = $this->ts($rawDate);
        // Firebase home events store Arabic display strings in `date` (not timestamps).
        $dateLabel = null;
        if ($parsedStarts === null && is_string($rawDate) && trim($rawDate) !== '') {
            $dateLabel = trim($rawDate);
        }
        $order = (int) ($d['order'] ?? $d['sort_order'] ?? 0);
        // Synthetic starts_at keeps list order when only a display label exists.
        $startsAt = $parsedStarts ?? date('Y-m-d H:i:s', strtotime('2020-01-01') + max(0, $order) * 86400);

        $audience = is_array($d['audience'] ?? null) ? $d['audience'] : [];
        if (array_key_exists('highlight', $d)) {
            $audience['highlight'] = (bool) $d['highlight'];
        }
        if ($dateLabel !== null) {
            $audience['date_label'] = $dateLabel;
        }
        if ($order > 0) {
            $audience['order'] = $order;
        }

        return [
            'subject_id' => $subjectId,
            'title' => $title,
            'starts_at' => $startsAt,
            'ends_at' => $this->ts($d['ends_at'] ?? $d['end'] ?? null),
            'weekday' => $d['weekday'] ?? $d['day'] ?? null,
            'audience' => $audience !== [] ? $audience : null,
            'created_by' => $createdBy,
        ];
    }

    public function eventAsSchedule(array $d, ?int $subjectId, int $createdBy): array
    {
        return $this->schedule($d, $subjectId, $createdBy);
    }

    public function user(array $d, ?string $preservePassword = null): array
    {
        $payload = [
            'firebase_uid' => $d['uid'] ?? $d['id'] ?? null,
            'name' => $d['name'] ?? $d['displayName'] ?? 'مستخدم',
            'email' => $d['email'],
            'phone' => $d['phone'] ?? null,
            'role' => $this->mapRole($d['role'] ?? 'student'),
            'theme_id' => $d['theme_id'] ?? $d['theme'] ?? null,
            'ornament_id' => $d['ornament_id'] ?? null,
            'is_active' => ($d['status'] ?? 'active') === 'active' && ($d['is_active'] ?? true),
            // Imported Firebase users must reset password on first Laravel login (FR migration).
            'must_reset_password' => true,
            'password' => Str::password(40),
        ];

        if ($preservePassword !== null && $preservePassword !== '') {
            $payload['password'] = $preservePassword;
            $payload['must_reset_password'] = false;
        }

        return $payload;
    }

    public function contact(array $d): array
    {
        return [
            'name' => $d['name'] ?? $d['senderName'] ?? null,
            'recipient' => $d['recipient'] ?? $d['to'] ?? null,
            'topic' => $d['topic'] ?? $d['subject'] ?? null,
            'body' => $d['body'] ?? $d['message'] ?? $d['text'] ?? '',
            'email' => $d['email'] ?? null,
            'meta' => ['legacy_id' => $d['id'] ?? null],
        ];
    }

    public function registration(array $d): array
    {
        return [
            'name' => $d['name'] ?? $d['fullName'] ?? 'طالبة',
            'phone' => $d['phone'] ?? $d['mobile'] ?? '',
            'email' => $d['email'] ?? ('import.'.Str::lower(Str::random(8)).'@mateen.import'),
            'age' => isset($d['age']) ? (int) $d['age'] : null,
            'level' => $d['level'] ?? $d['track'] ?? null,
            'source' => $d['source'] ?? $d['heardFrom'] ?? null,
            'status' => $d['status'] ?? 'new',
            'meta' => ['legacy_id' => $d['id'] ?? null],
        ];
    }

    public function plain(array $d, array $allowed): array
    {
        return array_intersect_key($d, array_flip($allowed));
    }

    private function mapRole(mixed $role): string
    {
        $role = strtolower(trim((string) $role));
        $allowed = ['admin', 'support', 'supervisor', 'teacher', 'student', 'mateen'];
        if (in_array($role, $allowed, true)) {
            return $role;
        }

        return 'student';
    }

    private function ts(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_array($v) && isset($v['_seconds'])) {
            return date('Y-m-d H:i:s', (int) $v['_seconds']);
        }
        if (is_numeric($v)) {
            $n = (int) $v;
            // Firestore JS timestamps are often milliseconds
            if ($n > 20000000000) {
                $n = (int) floor($n / 1000);
            }

            return date('Y-m-d H:i:s', $n);
        }
        $s = trim((string) $v);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s.' 00:00:00';
        }
        $t = strtotime($s);

        return $t ? date('Y-m-d H:i:s', $t) : null;
    }
}
