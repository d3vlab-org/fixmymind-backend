<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PsychometricTestsSeeder extends Seeder
{
    public function run(): void
    {
        $tests = [
            ['name' => 'BFI', 'slug' => 'bfi', 'description' => 'Big Five Inventory', 'related_problems' => ['osobowość'],
                'options' => [
                    ['label' => 'Zdecydowanie się nie zgadzam', 'value' => 1],
                    ['label' => 'Nie zgadzam się', 'value' => 2],
                    ['label' => 'Nie mam zdania', 'value' => 3],
                    ['label' => 'Zgadzam się', 'value' => 4],
                    ['label' => 'Zdecydowanie się zgadzam', 'value' => 5],
                ],
                'questions' => [
                    "Jestem osobą gadatliwą.",
                    "Mam skłonność do krytykowania innych.",
                    "Często czuję się zmartwiony lub niespokojny.",
                    "Mam bogatą wyobraźnię.",
                    "Jestem skrupulatny i sumienny.",
                ]
            ],
            ['name' => 'PHQ-9', 'slug' => 'phq9', 'description' => 'Depression assessment', 'related_problems' => ['depresja'],
                'options' => [
                    ['label' => 'Wcale', 'value' => 0],
                    ['label' => 'Kilka dni', 'value' => 1],
                    ['label' => 'Ponad połowę dni', 'value' => 2],
                    ['label' => 'Prawie codziennie', 'value' => 3],
                ],
                'questions' => [
                    "Mało interesowało Cię lub sprawiało Ci przyjemność robienie czegokolwiek.",
                    "Czułeś się przygnębiony, smutny lub bez nadziei.",
                    "Miałeś trudności ze snem lub spałeś zbyt dużo.",
                    "Czułeś się zmęczony lub miałeś mało energii.",
                    "Miałeś słaby apetyt lub objadałeś się.",
                ]
            ],
            ['name' => 'ISI', 'slug' => 'isi', 'description' => 'Insomnia Severity Index', 'related_problems' => ['bezsenność'],
                'options' => [
                    ['label' => 'Brak problemu', 'value' => 0],
                    ['label' => 'Lekki', 'value' => 1],
                    ['label' => 'Umiarkowany', 'value' => 2],
                    ['label' => 'Ciężki', 'value' => 3],
                    ['label' => 'Bardzo ciężki', 'value' => 4],
                ],
                'questions' => [
                    "Trudności z zasypianiem.",
                    "Problem z utrzymaniem snu.",
                    "Zbyt wczesne budzenie się.",
                    "Zadowolenie z obecnego wzorca snu.",
                    "Wpływ problemu ze snem na funkcjonowanie w ciągu dnia.",
                ]
            ],
        ];

        foreach ($tests as $test) {
            $id = DB::table('psychometric_tests')->insertGetId([
                'name' => $test['name'],
                'slug' => $test['slug'],
                'description' => $test['description'],
                'related_problems' => json_encode($test['related_problems']),
                'options' => json_encode($test['options']),
                'max_score' => count($test['questions']) * max(array_column($test['options'], 'value')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($test['questions'] as $i => $q) {
                DB::table('psychometric_questions')->insert([
                    'test_id' => $id,
                    'question_text' => $q,
                    'order' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
