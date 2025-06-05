<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Support\Facades\Http;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_chat_page_can_be_accessed_by_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/chat');
        
        $response->assertStatus(200);
        $response->assertViewIs('chat.index');
        $response->assertSee('AI Medical Assistant');
    }

    public function test_unauthenticated_user_cannot_access_chat()
    {
        $response = $this->get('/chat');
        
        $response->assertRedirect('/login');
    }

    public function test_can_get_available_models()
    {
        // Mock Ollama API response
        Http::fake([
            'localhost:11434/api/tags' => Http::response([
                'models' => [
                    ['name' => 'llama3.2:latest'],
                    ['name' => 'deepseek-r1:1.5b']
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/chat/models');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'models' => ['llama3.2:latest', 'deepseek-r1:1.5b']
        ]);
    }

    public function test_can_send_message_to_chat()
    {
        // Mock Ollama API response
        Http::fake([
            'localhost:11434/api/generate' => Http::response([
                'response' => 'Hello! I am MediAssist, your AI medical assistant. How can I help you today?'
            ], 200)
        ]);

        $messageData = [
            'message' => 'Hello, what can you help me with?',
            'model' => 'llama3.2'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/chat/send', $messageData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Hello, what can you help me with?',
            'response' => 'Hello! I am MediAssist, your AI medical assistant. How can I help you today?',
            'model' => 'llama3.2'
        ]);
    }

    public function test_chat_validates_required_message()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/chat/send', [
                'message' => '',
                'model' => 'llama3.2'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_chat_handles_ollama_connection_error()
    {
        // Mock Ollama API connection failure
        Http::fake([
            'localhost:11434/api/generate' => Http::response('Connection refused', 500)
        ]);

        $messageData = [
            'message' => 'Test message',
            'model' => 'llama3.2'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/chat/send', $messageData);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'error' => 'Failed to get response from AI model'
        ]);
    }

    public function test_can_clear_chat_history()
    {
        $response = $this->actingAs($this->user)
            ->post('/chat/clear');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Chat history cleared successfully'
        ]);
    }

    public function test_medical_prompt_includes_system_instructions()
    {
        Http::fake([
            'localhost:11434/api/generate' => Http::response([
                'response' => 'I understand you want medical advice. Please consult a healthcare professional.'
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/chat/send', [
                'message' => 'I have a headache',
                'model' => 'llama3.2'
            ]);

        $response->assertStatus(200);
        
        // Verify the request was made with medical system prompt
        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return str_contains($body['prompt'], 'MediAssist') && 
                   str_contains($body['prompt'], 'medical AI assistant') &&
                   str_contains($body['prompt'], 'I have a headache');
        });
    }
} 