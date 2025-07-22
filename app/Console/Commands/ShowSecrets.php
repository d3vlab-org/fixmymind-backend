<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowSecrets extends Command
{
    protected $signature = 'secrets:show';
    protected $description = 'Display important environment secrets in .env format';

    public function handle()
    {
        $keys = [
            'APP_KEY_DEV',
            'APP_KEY_PROD',
            'APP_KEY_TEST',
            'ELEVENLABS_API_KEY',
            'ELEVENLABS_VOICE_ID',
            'FLY_API_TOKEN',
            'OPENAI_API_KEY',
            'STRIPE_KEY',
            'STRIPE_SECRET',
            'STRIPE_WEBHOOK_SECRET',
            'SUPABASE_JWK_URL',
            'SUPABASE_KEY',
            'SUPABASE_URL',
        ];

        $this->info('--- ENV SECRETS ---');

        foreach ($keys as $key) {
            $value = getenv($key);
            $this->line("{$key}=" . ($value ?: '[not set]'));
        }

        $this->info('--- END ---');
    }
}
