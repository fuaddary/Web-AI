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
    
    // Configuration for conversation memory
    private const MAX_CONTEXT_LENGTH = 3000; // Maximum characters for conversation context
    private const MAX_HISTORY_MESSAGES = 20; // Maximum number of recent messages to consider
    private const EXTENDED_HISTORY_MESSAGES = 50; // Extended history for better context analysis

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
            // Get recent chat history for context (last 10 exchanges)
            $chatHistory = Chat::forUser($user->id)
                ->recent(self::MAX_HISTORY_MESSAGES)
                ->orderBy('created_at', 'asc')
                ->get();

            // Send request to Ollama API
            $response = Http::timeout(60)->post($this->ollamaUrl . '/api/generate', [
                'model' => $model,
                'prompt' => $this->buildMedicalPrompt($message, $chatHistory),
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

    /**
     * Get conversation context with intelligent summarization
     */
    private function getConversationContext($userId, $maxContextLength = 3000)
    {
        $chatHistory = Chat::forUser($userId)
            ->recent(50) // Get more history for better context
            ->orderBy('created_at', 'asc')
            ->get();

        if ($chatHistory->count() === 0) {
            return "";
        }

        $conversationContext = "\n\nRiwayat percakapan sebelumnya:\n";
        $totalLength = 0;
        $relevantHistory = [];
        
        // Reverse to process from newest to oldest
        $reversedHistory = $chatHistory->reverse();
        
        foreach ($reversedHistory as $chat) {
            $exchange = "Pengguna: " . $chat->message . "\nAsisten: " . $chat->response . "\n\n";
            $exchangeLength = strlen($exchange);
            
            if ($totalLength + $exchangeLength > $maxContextLength) {
                break; // Stop adding history if we're approaching the limit
            }
            
            $relevantHistory[] = $exchange;
            $totalLength += $exchangeLength;
        }
        
        // Reverse back to chronological order
        $relevantHistory = array_reverse($relevantHistory);
        
        // If we have older conversations that didn't fit, add a summary
        $excludedCount = $chatHistory->count() - count($relevantHistory);
        if ($excludedCount > 0) {
            $conversationContext .= "[Ringkasan: Terdapat {$excludedCount} percakapan sebelumnya yang membahas topik kesehatan terkait]\n\n";
        }
        
        foreach ($relevantHistory as $exchange) {
            $conversationContext .= $exchange;
        }
        
        $conversationContext .= "---\n\n";
        
        return $conversationContext;
    }

    private function buildMedicalPrompt($userMessage, $chatHistory = null)
    {
        $systemPrompt = "
        Kamu adalah seorang asisten kesehatan pribadi virtual yang profesional, suportif, dan berpengetahuan luas dalam bidang medis umum, kebugaran, nutrisi, kesehatan mental, dan gaya hidup sehat. Tugasmu adalah membantu pengguna memahami kondisi kesehatan mereka, memberi saran gaya hidup yang sehat, mendukung rutinitas kebugaran dan pola makan, serta mengingatkan untuk berkonsultasi dengan dokter bila diperlukan.
        Peranmu bukan untuk menggantikan dokter, tetapi untuk memberikan informasi awal, dukungan, dan panduan berdasarkan prinsip-prinsip kesehatan yang diakui secara umum.

        Terapkan prinsip berikut dalam setiap respons:
        - Gunakan bahasa yang ramah, sopan, dan mudah dipahami.
        - Jangan pernah memberikan diagnosis pasti atau resep medis.
        - Selalu dorong pengguna untuk berkonsultasi dengan tenaga medis profesional jika menyangkut gejala serius atau berkelanjutan.
        - Sesuaikan rekomendasi dengan konteks pengguna (misalnya usia, berat badan, aktivitas harian, atau preferensi diet bila tersedia).
        - Berikan penjelasan yang edukatif agar pengguna memahami alasan di balik saran yang diberikan.
        - Bila memungkinkan, tawarkan tips praktis dan motivasi jangka panjang untuk menjaga kesehatan.
        - Ingat dan referensikan informasi dari percakapan sebelumnya untuk memberikan saran yang konsisten dan personal.

        Siap membantu sebagai asisten kesehatan pribadi. Tanyakan kepada pengguna informasi penting seperti tujuan kesehatan, usia, jenis kelamin, dan kondisi medis yang perlu diperhatikan sebelum memberikan saran.
        ";

        $conversationContext = "";
        
        // Build conversation history if available
        if ($chatHistory && $chatHistory->count() > 0) {
            $conversationContext = "\n\nRiwayat percakapan sebelumnya:\n";
            
            // Limit conversation history to prevent token overflow
            // Keep only the most recent exchanges that fit within reasonable limits
            $totalLength = 0;
            $maxContextLength = self::MAX_CONTEXT_LENGTH;
            $relevantHistory = [];
            
            // Reverse to process from newest to oldest
            $reversedHistory = $chatHistory->reverse();
            
            foreach ($reversedHistory as $chat) {
                $exchange = "Pengguna: " . $chat->message . "\nAsisten: " . $chat->response . "\n\n";
                $exchangeLength = strlen($exchange);
                
                if ($totalLength + $exchangeLength > $maxContextLength) {
                    break; // Stop adding history if we're approaching the limit
                }
                
                $relevantHistory[] = $exchange;
                $totalLength += $exchangeLength;
            }
            
            // Reverse back to chronological order
            $relevantHistory = array_reverse($relevantHistory);
            
            // If we have older conversations that didn't fit, add a summary
            $excludedCount = $chatHistory->count() - count($relevantHistory);
            if ($excludedCount > 0) {
                $conversationContext .= "[Ringkasan: Terdapat {$excludedCount} percakapan sebelumnya yang membahas topik kesehatan terkait]\n\n";
            }
            
            foreach ($relevantHistory as $exchange) {
                $conversationContext .= $exchange;
            }
            
            $conversationContext .= "---\n\n";
        }
        
        return $systemPrompt . $conversationContext . "Pengguna: " . $userMessage . "\nAsisten: ";
    }
} 