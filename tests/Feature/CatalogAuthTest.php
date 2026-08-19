<?php

namespace Tests\Feature;

use App\Enums\MaterialType;
use App\Enums\Role;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\LibraryItem;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{admin: User, subject: Subject} */
    private function seedCatalog(): array
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $subject = Subject::query()->create([
            'slug' => 'fiqh',
            'title' => 'الفقه',
            'sort_order' => 1,
            'icon' => '⚖️',
        ]);
        LearningMaterial::query()->create([
            'subject_id' => $subject->id,
            'title' => 'محاضرة 1',
            'type' => MaterialType::Link,
            'url' => 'https://example.com/lecture.pdf',
            'created_by' => $admin->id,
            'sort_order' => 1,
        ]);
        LibraryItem::query()->create([
            'section' => 'mateen_library',
            'title' => 'مرجع سري',
            'media_url' => 'https://example.com/book.pdf',
            'created_by' => $admin->id,
            'sort_order' => 1,
        ]);

        return compact('admin', 'subject');
    }

    public function test_guest_can_list_subjects_but_not_materials_or_library(): void
    {
        ['subject' => $subject] = $this->seedCatalog();

        $this->getJson('/api/v1/subjects')->assertOk();
        $this->getJson("/api/v1/subjects/{$subject->id}/materials")->assertUnauthorized();
        $this->getJson('/api/v1/library')->assertUnauthorized();
    }

    public function test_enrolled_student_can_read_materials_and_library(): void
    {
        ['subject' => $subject] = $this->seedCatalog();
        $student = User::factory()->create(['role' => Role::Student]);
        Enrollment::query()->create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->getJson("/api/v1/subjects/{$subject->id}/materials")
            ->assertOk()
            ->assertJsonPath('data.0.url', 'https://example.com/lecture.pdf');

        $this->actingAs($student)
            ->getJson('/api/v1/library')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'مرجع سري');
    }

    public function test_unenrolled_student_cannot_read_materials(): void
    {
        ['subject' => $subject] = $this->seedCatalog();
        $student = User::factory()->create(['role' => Role::Student]);

        $this->actingAs($student)
            ->getJson("/api/v1/subjects/{$subject->id}/materials")
            ->assertForbidden();
    }
}
