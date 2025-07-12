/ === app/Jobs/SynthesizeAudioJob.php ===
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ElevenLabsService;
use App\Models\VoiceMessage;

class SynthesizeAudioJob implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

public $message;

public function __construct(VoiceMessage $message)
{
$this->message = $message;
}

public function handle()
{
$audioUrl = ElevenLabsService::synthesize($this->message->message_text);
$this->message->update(['audio_url' => $audioUrl]);
}
}
