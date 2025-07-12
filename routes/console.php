<?php


// === routes/console.php (test command) ===
use App\Models\VoiceMessage;
use App\Jobs\TranscribeVoiceJob;


Artisan::command('test:run-pipeline', function () {
    $msg = VoiceMessage::latest()->first();
    TranscribeVoiceJob::dispatch($msg);
    $this->info('Pipeline triggered.');
});
