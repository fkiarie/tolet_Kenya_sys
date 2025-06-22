<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->role)) {
                $user->role = 'user'; // Default role
            }
            if (is_null($user->is_active)) {
                $user->is_active = true; // Default to active
            }
        });
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get user's initials
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return $initials;
    }

    /**
     * Get avatar URL (placeholder for future implementation)
     */
    public function getAvatarUrlAttribute(): string
    {
        // You can implement Gravatar, upload system, or default avatars
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=random";
    }

    /**
     * Check if user can perform an action
     */
    public function can($ability, $arguments = []): bool
    {
        // Simple role-based permissions
        if ($this->isAdmin()) {
            return true; // Admin can do everything
        }

        if ($this->isManager()) {
            // Managers can manage most things
            $managerAbilities = [
                'landlords:view', 'landlords:manage',
                'buildings:view', 'buildings:manage',
                'units:view', 'units:manage',
                'tenants:view', 'tenants:manage',
            ];
            return in_array($ability, $managerAbilities);
        }

        // Regular users can only view
        $userAbilities = [
            'landlords:view',
            'buildings:view',
            'units:view',
            'tenants:view',
        ];
        
        return in_array($ability, $userAbilities);
    }

    /**
     * Get all available roles
     */
    public static function getRoles(): array
    {
        return [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'user' => 'User',
        ];
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        $roles = self::getRoles();
        return $roles[$this->role] ?? ucfirst($this->role);
    }
}