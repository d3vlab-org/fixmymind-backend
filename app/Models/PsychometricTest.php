<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychometricTest extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'related_problems', 'options', 'max_score'];

    protected $casts = [
        'related_problems' => 'array',
        'options' => 'array',
    ];

    public function questions()
    {
        return $this->hasMany(PsychometricQuestion::class, 'test_id');
    }
}
