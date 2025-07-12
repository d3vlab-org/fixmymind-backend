<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserTestResult extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'test_id',
        'responses',
        'score',
        'interpretation',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'score' => 'float',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function psychometricTest()
    {
        return $this->belongsTo(PsychometricTest::class, 'test_id');
    }
}

