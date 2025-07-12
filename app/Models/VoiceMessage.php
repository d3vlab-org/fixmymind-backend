<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VoiceMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'voice_session_id',
        'sender',
        'text',
        'audio_url',
        'timestamp',
    ];

    public function session()
    {
        return $this->belongsTo(VoiceSession::class);
    }
}
