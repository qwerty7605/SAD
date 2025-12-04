<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClearanceItem extends Model
{
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'clearance_id',
        'org_id',
        'required_signatory_id',
        'status',
        'approved_by',
        'approved_date',
        'is_auto_approved',
    ];

    protected function casts(): array
    {
        return [
            'approved_date' => 'datetime',
            'is_auto_approved' => 'boolean',
            'created_at' => 'datetime',
            'status_updated' => 'datetime',
        ];
    }

    // Relationships
    public function clearance(): BelongsTo
    {
        return $this->belongsTo(StudentClearance::class, 'clearance_id', 'clearance_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id', 'org_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(OrganizationAdmin::class, 'approved_by', 'admin_id');
    }

    public function requiredSignatory(): BelongsTo
    {
        return $this->belongsTo(OrganizationAdmin::class, 'required_signatory_id', 'admin_id');
    }
}
