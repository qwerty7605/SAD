<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAdmin extends Model
{
    protected $primaryKey = 'sys_admin_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'sys_admin_id',
        'admin_level',
        'full_name',
        'department',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sys_admin_id', 'user_id');
    }
}
