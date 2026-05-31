<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'branch_id',
        'pin_code',
        'is_active',
        'phone',
        'status',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

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
            'is_active' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'Super Admin';
    }

    public function isManager(): bool
    {
        return $this->role?->name === 'Manager';
    }

    public function isCashier(): bool
    {
        return $this->role?->name === 'Cashier';
    }

    public function isWaiter(): bool
    {
        return $this->role?->name === 'Waiter';
    }

    public function isKitchen(): bool
    {
        return $this->role?->name === 'Kitchen Staff';
    }

    public function isStoreKeeper(): bool
    {
        return $this->role?->name === 'Store Keeper';
    }

    public function isAccountant(): bool
    {
        return $this->role?->name === 'Accountant';
    }
}
