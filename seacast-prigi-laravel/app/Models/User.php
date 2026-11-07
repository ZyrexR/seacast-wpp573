<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    /**
     * Determine if the user has a specific role or one of given roles.
     * Supports both string and UserRole enum values.
     */
    public function hasRole(string|array|UserRole $roles): bool
    {
        if ($roles instanceof UserRole) {
            return $this->role === $roles->value;
        }

        if (is_string($roles)) {
            return $this->role === $roles;
        }

        $normalized = array_map(
            fn ($r) => $r instanceof UserRole ? $r->value : (string) $r,
            $roles
        );

        return in_array($this->role, $normalized, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN);
    }

    public function isNelayan(): bool
    {
        return $this->hasRole(UserRole::NELAYAN);
    }

    public function isSyahbandar(): bool
    {
        return $this->hasRole(UserRole::SYAHBANDAR);
    }

    public function isPeneliti(): bool
    {
        return $this->hasRole(UserRole::PENELITI);
    }
}
