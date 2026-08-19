<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_account_status(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin, 'account_status' => 'active']);
        $user = User::factory()->create(['role' => Role::Student, 'account_status' => 'active']);

        $this->actingAs($admin)
            ->patchJson("/api/v1/users/{$user->id}", ['status' => 'suspended'])
            ->assertOk()
            ->assertJsonPath('data.account_status', 'suspended')
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_status' => 'suspended',
            'is_active' => false,
        ]);
    }
}
