<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $ollamaUrl = 'http://localhost:11434';

    public function index()
    {
        $user = Auth::user();
        
        // Get recent chat history
        $chatHistory = Chat::forUser($user->id)
            ->recent(20)
            ->orderBy('created_at', 'asc')
            ->get();

        // Check Ollama status
        $ollamaStatus = $this->checkOllamaStatus();

        return view('chat.index', compact('chatHistory', 'ollamaStatus'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'model' => 'nullable|string|max:50'
        ]);

        $user = Auth::user();
        $message = $request->input('message');
        $model = $request->input('model', 'llama3.2'); // Default model

        try {
            // Send request to Ollama API
            $response = Http::timeout(60)->post($this->ollamaUrl . '/api/generate', [
                'model' => $model,
                'prompt' => $this->buildMedicalPrompt($message),
                'stream' => false,
                'options' => [
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                ]
            ]);

            if ($response->successful()) {
                $aiResponse = $response->json()['response'] ?? 'No response received';
                
                // Save chat to database
                $chat = Chat::create([
                    'user_id' => $user->id,
                    'message' => $message,
                    'response' => $aiResponse,
                    'model' => $model,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'response' => $aiResponse,
                    'model' => $model,
                    'timestamp' => $chat->created_at->format('M j, H:i')
                ]);
            } else {
                Log::error('Ollama API error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to get response from AI model'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Unable to connect to AI service. Please ensure Ollama is running.'
            ], 500);
        }
    }

    public function clearHistory()
    {
        $user = Auth::user();
        Chat::forUser($user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared successfully'
        ]);
    }

    public function getModels()
    {
        try {
            $response = Http::timeout(10)->get($this->ollamaUrl . '/api/tags');
            
            if ($response->successful()) {
                $models = collect($response->json()['models'] ?? [])
                    ->pluck('name')
                    ->toArray();
                
                return response()->json([
                    'success' => true,
                    'models' => $models
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch models: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'models' => ['llama3.2'] // Fallback model
        ]);
    }

    private function checkOllamaStatus()
    {
        try {
            $response = Http::timeout(5)->get($this->ollamaUrl . '/api/tags');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function buildMedicalPrompt($userMessage)
    {
        $systemPrompt = "You are MediAssist, a helpful medical AI assistant. You provide general health information and guidance, but always remind users to consult with healthcare professionals for serious concerns. You are knowledgeable about symptoms, general health advice, medications (general information), and healthy lifestyle recommendations. Always be empathetic, professional, and clear that you cannot replace professional medical diagnosis or treatment.\n\nUser question: ";
        
        return $systemPrompt . $userMessage;
    }
} 