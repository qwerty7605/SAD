<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $primaryKey = 'org_id';
    public $timestamps = false;

    protected $fillable = [
        'org_code',
        'org_name',
        'org_type',
        'department',
        'is_active',
        'requires_clearance',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_clearance' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // Relationships
    public function admin(): HasOne
    {
        return $this->hasOne(OrganizationAdmin::class, 'org_id', 'org_id');
    }

    public function clearanceItems(): HasMany
    {
        return $this->hasMany(ClearanceItem::class, 'org_id', 'org_id');
    }
}
