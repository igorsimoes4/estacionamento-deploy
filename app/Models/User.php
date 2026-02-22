<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject {
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_OPERATOR = 'operador';
    public const ROLE_FINANCIAL = 'financeiro';

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
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasRole(string $role): bool
    {
        return strtolower((string) ($this->role ?: self::ROLE_ADMIN)) === strtolower($role);
    }

    public function hasAnyRole(array $roles): bool
    {
        $normalized = array_map(static fn ($role) => strtolower((string) $role), $roles);
        return in_array(strtolower((string) ($this->role ?: self::ROLE_ADMIN)), $normalized, true);
    }

    public function cashShifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }
}
