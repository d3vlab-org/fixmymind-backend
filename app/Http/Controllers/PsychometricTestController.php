<?php

namespace App\Http\Controllers;

use App\Models\PsychometricTest;
use App\Models\PsychometricQuestion;
use App\Models\PsychometricScore;
use Illuminate\Http\Request;

class PsychometricTestController extends Controller
{
    public function suggest(Request $request)
    {
        return response()->json(PsychometricTest::all());
    }

    public function show($slug)
    {
        $test = PsychometricTest::where('slug', $slug)->firstOrFail();
        $questions = $test->questions()->orderBy('order')->get();

        return response()->json([
            'test' => $test->name,
            'description' => $test->description,
            'questions' => $questions,
            'options' => $test->options,
        ]);
    }

    public function index()
    {
        $tests = PsychometricTest::select('id', 'name', 'description', 'related_problems')->get();

        return response()->json($tests);
    }

//    public function submit(Request $request, $id)
//    {
//        return response()->json([
//            'origin' => $request->header('Origin'),
//            'host' => $request->header('Host'),
//            // ...
//        ]);
//    }

    public function submit(Request $request, $testId)
    {

        $validated = $request->validate([
            'score' => 'required|numeric',
            'answers' => 'required|array',
        ]);

        $test = PsychometricTest::findOrFail($testId);

        // Interpretacja wyniku – placeholder, możesz rozwinąć algorytm
        $interpretation = $this->interpretScore($test->name, $validated['score']);

        $score = PsychometricScore::create([
            'user_id' => '00000000-0000-0000-0000-000000000001', // <- tymczasowy UUID
            'test_id' => $test->id,
            'score' => $validated['score'],
            'answers' => json_encode($validated['answers']),
            'interpretation' => $interpretation,
        ]);

        return response()->json([
            'message' => 'Wynik zapisany.',
            'interpretation' => $interpretation,
        ]);
    }

    private function interpretScore(string $testName, float $score): string
    {
        switch ($testName) {
            case 'PHQ-9':
                if ($score <= 4) return 'Minimalne objawy depresji';
                if ($score <= 9) return 'Łagodna depresja';
                if ($score <= 14) return 'Umiarkowana depresja';
                if ($score <= 19) return 'Umiarkowanie ciężka depresja';
                return 'Ciężka depresja';
            // Dodaj inne testy
            default:
                return 'Brak interpretacji';
        }
    }

    public function questions($id)
    {
        $test = PsychometricTest::with('questions')->findOrFail($id);

        $questions = $test->questions->map(function ($q) use ($test) {
            return [
                'id' => $q->id,
                'test_id' => $q->test_id,
                'question_text' => $q->question_text,
                'order' => $q->order,
                'options' => $test->options, // dodaj opcje globalne testu
            ];
        });

        return response()->json([
            'test' => $test->name,
            'questions' => $questions,
        ]);
    }


}
