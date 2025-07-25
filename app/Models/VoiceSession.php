<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VoiceSession extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function messages()
    {
        return $this->hasMany(VoiceMessage::class);
    }
}
