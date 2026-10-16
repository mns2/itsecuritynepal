<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Staff accounts. No public registration: admins are created with `php artisan make:filament-user`
 * (or the seeder from ADMIN_* env vars); instructor accounts are created by an admin in Settings → Staff accounts.
 *  - admin, manager, office, marketing, editor → /admin, limited to their areas (App\Support\Access)
 *  - instructor → /trainer (only their own classes: students and attendance)
 */
#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'instructor_id', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** Labels for each role; permissions per role live in App\Support\Access. */
    public const ROLES = \App\Support\Access::ROLES;

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => in_array($this->role, \App\Support\Access::adminRoles(), true),
            'trainer' => $this->role === 'instructor' || \App\Support\Access::allows('training', $this),
            default => false,
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(TrainingClass::class, 'instructor_user_id');
    }
}
