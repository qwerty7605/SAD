<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'password_hash',
        'email',
        'user_type',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'last_login' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'student_id', 'user_id');
    }

    public function organizationAdmin(): HasOne
    {
        return $this->hasOne(OrganizationAdmin::class, 'admin_id', 'user_id');
    }

    public function systemAdmin(): HasOne
    {
        return $this->hasOne(SystemAdmin::class, 'sys_admin_id', 'user_id');
    }
}
