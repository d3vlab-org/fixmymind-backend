<?phpphp

// === app/Services/WhisperService.php ===
namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhisperService
{
public static function transcribe($filePath)
{
$response = Http::withToken(config('services.openai.key'))
->attach(
'file',
file_get_contents(storage_path('app/' . $filePath)),
'audio.m4a'
)->post('https://api.openai.com/v1/audio/transcriptions', [
'model' => 'whisper-1'
]);

return $response->json('text');
}
