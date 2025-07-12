<?php

namespace App\Http\Controllers;

use App\Models\TherapistPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Therapist Preferences",
 *     description="Operations related to therapist style preferences"
 * )
 */
class TherapistPreferenceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/therapist-preferences",
     *     summary="Get user's therapist preferences",
     *     tags={"Therapist Preferences"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Therapist preferences retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Alex"),
     *                 @OA\Property(property="style", type="string", example="warm"),
     *                 @OA\Property(property="tempo", type="string", example="slow"),
     *                 @OA\Property(property="tone", type="string", example="soft"),
     *                 @OA\Property(property="add_markers", type="boolean", example=true),
     *                 @OA\Property(property="ask_questions", type="boolean", example=true),
     *                 @OA\Property(property="generated_prompt", type="string", example="Jesteś terapeutą o imieniu Alex...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No preferences found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No therapist preferences found")
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();
            $preferences = TherapistPreference::where('user_id', $user->id)->first();

            if (!$preferences) {
                return response()->json([
                    'success' => false,
                    'message' => 'No therapist preferences found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $preferences->id,
                    'name' => $preferences->name,
                    'style' => $preferences->style,
                    'tempo' => $preferences->tempo,
                    'tone' => $preferences->tone,
                    'add_markers' => $preferences->add_markers,
                    'ask_questions' => $preferences->ask_questions,
                    'generated_prompt' => $preferences->generatePrompt(),
                    'created_at' => $preferences->created_at,
                    'updated_at' => $preferences->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving therapist preferences', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving therapist preferences'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/therapist-preferences",
     *     summary="Create or update therapist preferences",
     *     tags={"Therapist Preferences"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Alex", description="Therapist name"),
     *             @OA\Property(property="style", type="string", example="warm", enum={"warm", "calm", "analytical", "motivating", "deep"}),
     *             @OA\Property(property="tempo", type="string", example="slow", enum={"slow", "medium", "fast"}),
     *             @OA\Property(property="tone", type="string", example="soft", enum={"soft", "neutral", "firm"}),
     *             @OA\Property(property="add_markers", type="boolean", example=true, description="Add pauses and expressions like 'I understand', 'hmm'"),
     *             @OA\Property(property="ask_questions", type="boolean", example=true, description="Ask reflective questions")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Therapist preferences saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Therapist preferences saved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Alex"),
     *                 @OA\Property(property="style", type="string", example="warm"),
     *                 @OA\Property(property="tempo", type="string", example="slow"),
     *                 @OA\Property(property="tone", type="string", example="soft"),
     *                 @OA\Property(property="add_markers", type="boolean", example=true),
     *                 @OA\Property(property="ask_questions", type="boolean", example=true),
     *                 @OA\Property(property="generated_prompt", type="string", example="Jesteś terapeutą o imieniu Alex...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'style' => ['required', Rule::in(['warm', 'calm', 'analytical', 'motivating', 'deep'])],
                'tempo' => ['required', Rule::in(['slow', 'medium', 'fast'])],
                'tone' => ['required', Rule::in(['soft', 'neutral', 'firm'])],
                'add_markers' => 'required|boolean',
                'ask_questions' => 'required|boolean',
            ]);

            $preferences = TherapistPreference::updateOrCreate(
                ['user_id' => $user->id],
                $validated
            );

            Log::info('Therapist preferences saved', [
                'user_id' => $user->id,
                'preferences_id' => $preferences->id,
                'preferences' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Therapist preferences saved successfully',
                'data' => [
                    'id' => $preferences->id,
                    'name' => $preferences->name,
                    'style' => $preferences->style,
                    'tempo' => $preferences->tempo,
                    'tone' => $preferences->tone,
                    'add_markers' => $preferences->add_markers,
                    'ask_questions' => $preferences->ask_questions,
                    'generated_prompt' => $preferences->generatePrompt(),
                    'created_at' => $preferences->created_at,
                    'updated_at' => $preferences->updated_at,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving therapist preferences', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving therapist preferences'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/therapist-preferences/options",
     *     summary="Get available options for therapist preferences",
     *     tags={"Therapist Preferences"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Available options retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="styles", type="object",
     *                     @OA\Property(property="warm", type="string", example="Ciepły i wspierający"),
     *                     @OA\Property(property="calm", type="string", example="Spokojny i refleksyjny")
     *                 ),
     *                 @OA\Property(property="tempos", type="object",
     *                     @OA\Property(property="slow", type="string", example="Wolno"),
     *                     @OA\Property(property="medium", type="string", example="Średnio")
     *                 ),
     *                 @OA\Property(property="tones", type="object",
     *                     @OA\Property(property="soft", type="string", example="Miękki"),
     *                     @OA\Property(property="neutral", type="string", example="Neutralny")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function options(): JsonResponse
    {
        $preference = new TherapistPreference();
        
        return response()->json([
            'success' => true,
            'data' => [
                'styles' => $preference->getStyleOptions(),
                'tempos' => $preference->getTempoOptions(),
                'tones' => $preference->getToneOptions(),
            ]
        ]);
    }
}
