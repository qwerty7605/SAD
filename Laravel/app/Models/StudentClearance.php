<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentClearance extends Model
{
    protected $primaryKey = 'clearance_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'term_id',
        'overall_status',
        'approved_date',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'last_updated' => 'datetime',
            'approved_date' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id', 'term_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClearanceItem::class, 'clearance_id', 'clearance_id');
    }
}
