<?php

namespace App\Services\FirebaseMigration;

use Illuminate\Support\Str;

class DocumentMapper
{
    public function user(array $d): array
    {
        return [
            'firebase_uid' => $d['uid'] ?? $d['id'] ?? null,
            'name' => $d['name'] ?? $d['displayName'] ?? 'مستخدم',
            'email' => $d['email'],
            'phone' => $d['phone'] ?? null,
            'role' => $d['role'] ?? 'student',
            'theme_id' => $d['theme_id'] ?? $d['theme'] ?? null,
            'ornament_id' => $d['ornament_id'] ?? null,
            'is_active' => ($d['status'] ?? 'active') === 'active' && ($d['is_active'] ?? true),
            // Imported Firebase users must reset password on first Laravel login (FR migration).
            'must_reset_password' => true,
            'password' => Str::password(40),
        ];
    }

    public function subject(array $d): array
    {
        return [
            'slug' => $d['slug'] ?? $d['id'],
            'title' => $d['title'] ?? $d['name'] ?? $d['id'],
            'subtitle' => $d['subtitle'] ?? null,
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
            'type' => $d['type'] ?? 'محاضرة',
            'body' => $d['body'] ?? $d['content'] ?? $d['description'] ?? null,
            'url' => $d['url'] ?? $d['link'] ?? null,
            'created_by' => $createdBy,
            'sort_order' => $d['sort_order'] ?? $d['order'] ?? 0,
        ];
    }

    public function studentProfile(array $d, int $userId): array
    {
        return [
            'user_id' => $userId,
            'interview_status' => $d['interview_status'] ?? $d['interviewStatus'] ?? 'not_done',
            'status_class' => $d['status_class'] ?? $d['statusClass'] ?? 'newcomer',
            'notes' => $d['notes'] ?? null,
            'extra' => array_filter([
                'legacy_id' => $d['id'] ?? null,
                'name' => $d['name'] ?? null,
                'order' => $d['order'] ?? null,
            ]),
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
        return [
            'title' => $d['title'] ?? 'خبر',
            'body' => $d['body'] ?? $d['content'] ?? '',
            'status' => $d['status'] ?? 'published',
            'published_at' => $this->ts($d['published_at'] ?? $d['createdAt'] ?? null),
            'created_by' => $createdBy,
        ];
    }

    public function schedule(array $d, ?int $subjectId, int $createdBy): array
    {
        return [
            'subject_id' => $subjectId,
            'title' => $d['title'] ?? 'موعد',
            'starts_at' => $this->ts($d['starts_at'] ?? $d['start'] ?? now()) ?? now(),
            'ends_at' => $this->ts($d['ends_at'] ?? $d['end'] ?? null),
            'weekday' => $d['weekday'] ?? $d['day'] ?? null,
            'audience' => $d['audience'] ?? null,
            'created_by' => $createdBy,
        ];
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

    private function ts(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_array($v) && isset($v['_seconds'])) {
            return date('Y-m-d H:i:s', (int) $v['_seconds']);
        }
        if (is_numeric($v)) {
            return date('Y-m-d H:i:s', (int) $v);
        }

        return (string) $v;
    }
}
