<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationAdmin extends Model
{
    protected $primaryKey = 'admin_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'org_id',
        'position',
        'full_name',
        'removed_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'datetime',
            'removed_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id', 'org_id');
    }

    public function approvedClearances(): HasMany
    {
        return $this->hasMany(ClearanceItem::class, 'approved_by', 'admin_id');
    }
}
