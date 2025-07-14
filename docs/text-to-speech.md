# Text-to-Speech Feature Documentation

This document describes the Text-to-Speech feature implemented in the FixMyMind application, which converts text messages to audio using the ElevenLabs API.

## Components

### 1. ElevenLabsService

A service class that interacts with the ElevenLabs API to convert text to speech.

**File**: `app/Services/ElevenLabsService.php`

**Usage**:
```php
use App\Services\ElevenLabsService;

// Convert text to speech and get the URL to the audio file
$audioUrl = ElevenLabsService::synthesize('Text to convert to speech');
```

### 2. GenerateSpeech Job

A job that takes a voice message ID, retrieves the text, generates audio using ElevenLabs, and updates the message with the audio URL.

**File**: `app/Jobs/GenerateSpeech.php`

**Usage**:
```php
use App\Jobs\GenerateSpeech;

// Dispatch the job to generate speech for a voice message
GenerateSpeech::dispatch($voiceMessageId);
```

### 3. AudioReady Event

An event that is emitted when the speech generation is complete, containing the message ID and the URL to the audio file.

**File**: `app/Events/AudioReady.php`

**Usage in PHP**:
```php
use App\Events\AudioReady;

// Emit the event
event(new AudioReady($messageId, $audioUrl));
```

**Usage in JavaScript**:
```javascript
// In your JavaScript client code:
const channel = Echo.channel('audio');
channel.listen('AudioReady', (event) => {
    console.log('Audio is ready:', event.audio_url);
    console.log('For message ID:', event.message_id);
    
    // Update your UI with the audio player
    const audioPlayer = document.getElementById('audio-player');
    audioPlayer.src = event.audio_url;
    audioPlayer.style.display = 'block';
});
```

## Configuration

The ElevenLabs API requires an API key and a voice ID. These are configured in the `.env` file:

```
ELEVENLABS_API_KEY=your-api-key
ELEVENLABS_VOICE_ID=your-voice-id
```

And in the `config/services.php` file:

```php
'elevenlabs' => [
    'key' => env('ELEVENLABS_API_KEY'),
    'voice_id' => env('ELEVENLABS_VOICE_ID', 'default'),
],
```

## Example Usage

### Controller Method

```php
public function generateSpeechForMessage($messageId)
{
    // Check if the voice message exists
    $voiceMessage = VoiceMessage::find($messageId);
    
    if (!$voiceMessage) {
        return response()->json(['error' => 'Voice message not found'], 404);
    }
    
    // Check if the voice message has text
    if (empty($voiceMessage->text)) {
        return response()->json(['error' => 'Voice message has no text to convert to speech'], 400);
    }
    
    // Dispatch the job to generate speech
    GenerateSpeech::dispatch($messageId);
    
    return response()->json([
        'message' => 'Speech generation job has been dispatched',
        'voice_message_id' => $messageId
    ]);
}
```

## Testing

The feature includes tests to ensure that the job can be dispatched and processes correctly:

```bash
php artisan test --filter=GenerateSpeechTest
```

## Troubleshooting

If you encounter issues with the text-to-speech feature, check the following:

1. Ensure that the ElevenLabs API key and voice ID are correctly configured in the `.env` file.
2. Check the Laravel logs for any errors related to the ElevenLabs API.
3. Verify that the voice message has text content to convert to speech.
4. Ensure that the storage directory is writable by the web server.
