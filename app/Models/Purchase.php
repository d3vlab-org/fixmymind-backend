<?php

// app/Models/Purchase.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'plan_id',
        'amount',
        'status',
        'valid_until',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
