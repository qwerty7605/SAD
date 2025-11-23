<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'student_number',
        'first_name',
        'middle_name',
        'last_name',
        'course',
        'year_level',
        'section',
        'contact_number',
        'date_enrolled',
        'enrollment_status',
    ];

    protected function casts(): array
    {
        return [
            'date_enrolled' => 'date',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(StudentClearance::class, 'student_id', 'student_id');
    }
}
