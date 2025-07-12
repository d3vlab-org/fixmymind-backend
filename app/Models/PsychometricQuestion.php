<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychometricQuestion extends Model
{
    protected $fillable = ['test_id', 'question_text', 'order'];

    public function test()
    {
        return $this->belongsTo(PsychometricTest::class, 'test_id');
    }
}
