<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'phone',
        'avatar_url',
        'role',
        'is_active',
        'is_verified',
        'otp_code',
        'otp_expires_at',
        'last_reengagement_email_sent_at',
        'password_reset_requested_at',
        'password_reset_completed_at',
        'marketing_emails_enabled',
        'email_verified_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'last_reengagement_email_sent_at' => 'datetime',
            'password_reset_requested_at' => 'datetime',
            'password_reset_completed_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'marketing_emails_enabled' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * A user can have many login history records.  
     */
    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'performed_by');
    }

    public function cartRecovery()
    {
        return $this->hasOne(CartRecovery::class);
    }
}
