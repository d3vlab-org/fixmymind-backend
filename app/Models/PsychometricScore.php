<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychometricScore extends Model
{
    protected $fillable = ['user_id', 'test_id', 'answers', 'score','interpretation'];

    protected $casts = [
        'answers' => 'array',
    ];

    public function test()
    {
        return $this->belongsTo(PsychometricTest::class, 'test_id');
    }
}
