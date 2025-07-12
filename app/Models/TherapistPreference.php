<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapistPreference extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'style',
        'tempo',
        'tone',
        'add_markers',
        'ask_questions',
    ];

    protected $casts = [
        'add_markers' => 'boolean',
        'ask_questions' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStyleOptions(): array
    {
        return [
            'warm' => 'Ciepły i wspierający',
            'calm' => 'Spokojny i refleksyjny',
            'analytical' => 'Dociekliwy i analityczny',
            'motivating' => 'Energiczny i motywujący',
            'deep' => 'Filozoficzny i głęboki',
        ];
    }

    public function getTempoOptions(): array
    {
        return [
            'slow' => 'Wolno',
            'medium' => 'Średnio',
            'fast' => 'Szybko',
        ];
    }

    public function getToneOptions(): array
    {
        return [
            'soft' => 'Miękki',
            'neutral' => 'Neutralny',
            'firm' => 'Stanowczy',
        ];
    }

    public function generatePrompt(): string
    {
        $styleDescriptions = $this->getStyleOptions();
        $tempoDescriptions = $this->getTempoOptions();
        $toneDescriptions = $this->getToneOptions();

        $prompt = "Jesteś terapeutą o imieniu {$this->name}. ";
        $prompt .= "Twój styl to: {$styleDescriptions[$this->style]}. ";
        $prompt .= "Tempo mówienia: {$tempoDescriptions[$this->tempo]}. ";
        $prompt .= "Ton głosu: {$toneDescriptions[$this->tone]}. ";
        
        if ($this->add_markers) {
            $prompt .= "Dodawaj pauzy i wyrażenia typu 'rozumiem', 'hmm' w odpowiednich momentach. ";
        }
        
        if ($this->ask_questions) {
            $prompt .= "Zadawaj pytania skłaniające do refleksji. ";
        }

        $prompt .= "Prowadź terapię w sposób profesjonalny i empatyczny.";

        return $prompt;
    }
}
