<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicTerm extends Model
{
    protected $primaryKey = 'term_id';
    public $timestamps = false;

    protected $fillable = [
        'academic_year',
        'semester',
        'term_name',
        'start_date',
        'end_date',
        'enrollment_start',
        'enrollment_end',
        'is_current',
        'clearance_deadline',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'enrollment_start' => 'date',
            'enrollment_end' => 'date',
            'clearance_deadline' => 'date',
            'is_current' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // Relationships
    public function studentClearances(): HasMany
    {
        return $this->hasMany(StudentClearance::class, 'term_id', 'term_id');
    }
}
