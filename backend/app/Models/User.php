<?php

namespace App\Models;

use App\Enums\Role;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['firebase_uid','name','email','password','phone','role','subject_id','theme_id','ornament_id','is_active','must_reset_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function subject() { return $this->belongsTo(Subject::class); }
    public function studentProfile() { return $this->hasOne(StudentProfile::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function messages() { return $this->hasMany(Message::class, 'sender_id'); }
    public function devices() { return $this->hasMany(UserDevice::class); }
    public function conversations() { return $this->belongsToMany(Conversation::class); }
    public function sendPasswordResetNotification($token): void { $this->notify(new ResetPasswordNotification($token)); }
    public function isRole(Role|string ...$roles): bool {
        return collect($roles)->contains(fn ($role) => $this->role === ($role instanceof Role ? $role : Role::from($role)));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
            'must_reset_password' => 'boolean',
        ];
    }
}
