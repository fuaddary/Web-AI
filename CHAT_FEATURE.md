# AI Medical Assistant Chat Feature

## Overview

The AI Medical Assistant Chat feature integrates with Ollama to provide an intelligent medical assistant within the MediAssist application. Users can ask health-related questions and receive AI-powered responses from local LLM models.

## Features

### 🤖 AI Integration
- **Ollama API Integration**: Connects to local Ollama instance on `localhost:11434`
- **Multiple Model Support**: Automatically detects and allows selection of available models
- **Medical-Focused Prompts**: Custom system prompts designed for medical assistance

### 💬 Chat Interface
- **Real-time Messaging**: Instant messaging interface with typing indicators
- **Chat History**: Persistent chat history stored in MongoDB
- **Model Selection**: Choose between available Ollama models (llama3.2, deepseek-r1, etc.)
- **Connection Status**: Visual indicator showing Ollama connection status

### 🛡️ Safety Features
- **Medical Disclaimers**: Clear disclaimers about AI limitations
- **Professional Guidance**: Automatic reminders to consult healthcare professionals
- **Responsible AI**: System prompts emphasize general information only

## Technical Implementation

### Models & Database
```php
// Chat Model (MongoDB)
class Chat extends Model
{
    protected $fillable = ['user_id', 'message', 'response', 'model'];
    
    // Relationships and scopes for user-specific chat history
    public function user() { return $this->belongsTo(User::class); }
    public function scopeForUser($query, $userId) { /* ... */ }
    public function scopeRecent($query, $limit = 50) { /* ... */ }
}
```

### API Integration
```php
// ChatController - Ollama Integration
class ChatController extends Controller
{
    private $ollamaUrl = 'http://localhost:11434';
    
    public function sendMessage(Request $request) {
        // Validates input, sends to Ollama, saves to database
        $response = Http::timeout(60)->post($this->ollamaUrl . '/api/generate', [
            'model' => $model,
            'prompt' => $this->buildMedicalPrompt($message),
            'stream' => false,
        ]);
    }
}
```

### Routes
```php
// Web Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/clear', [ChatController::class, 'clearHistory'])->name('chat.clear');
    Route::get('/chat/models', [ChatController::class, 'getModels'])->name('chat.models');
});
```

## Available Endpoints

### GET `/chat`
**Purpose**: Display the chat interface  
**Authentication**: Required  
**Response**: Chat view with history and Ollama status

### POST `/chat/send`
**Purpose**: Send message to AI assistant  
**Authentication**: Required  
**Payload**:
```json
{
    "message": "What are common causes of headaches?",
    "model": "llama3.2"
}
```
**Response**:
```json
{
    "success": true,
    "message": "What are common causes of headaches?",
    "response": "Common causes include tension, stress, dehydration...",
    "model": "llama3.2",
    "timestamp": "Dec 5, 14:30"
}
```

### POST `/chat/clear`
**Purpose**: Clear user's chat history  
**Authentication**: Required  
**Response**: Success confirmation

### GET `/chat/models`
**Purpose**: Get available Ollama models  
**Authentication**: Required  
**Response**:
```json
{
    "success": true,
    "models": ["llama3.2:latest", "deepseek-r1:1.5b", "deepseek-r1:7b"]
}
```

## UI Components

### Chat Interface Features
- **Modern Design**: Clean, medical-themed interface with gradient headers
- **Message Bubbles**: Distinct styling for user and AI messages
- **Model Indicators**: Shows which model generated each response
- **Typing Animation**: Visual feedback during AI processing
- **Auto-scroll**: Automatic scrolling to latest messages
- **Responsive Design**: Works on desktop and mobile devices

### Navigation Integration
- **Menu Item**: "AI Chat" added to main navigation
- **Active States**: Proper highlighting when on chat pages
- **Responsive Menu**: Mobile-friendly navigation

## Medical Prompt Engineering

### System Prompt
```
You are MediAssist, a helpful medical AI assistant. You provide general health 
information and guidance, but always remind users to consult with healthcare 
professionals for serious concerns. You are knowledgeable about symptoms, 
general health advice, medications (general information), and healthy lifestyle 
recommendations. Always be empathetic, professional, and clear that you cannot 
replace professional medical diagnosis or treatment.

User question: [USER_MESSAGE]
```

### Safety Guidelines
- **Disclaimer Display**: Prominent disclaimer on every chat session
- **Professional Reminders**: AI responses include healthcare consultation reminders
- **General Information Only**: Clear boundaries about AI capabilities
- **Emergency Guidance**: Directs users to emergency services when appropriate

## Testing

### Comprehensive Test Suite
```bash
php artisan test tests/Feature/ChatTest.php
```

**Test Coverage:**
- ✅ Authentication requirements
- ✅ Chat page accessibility
- ✅ Message sending and validation
- ✅ Ollama API integration
- ✅ Error handling
- ✅ Model selection
- ✅ Chat history management
- ✅ Medical prompt verification

### Test Results
```
Tests:    8 passed (18 assertions)
Duration: 0.33s
```

## Setup Requirements

### Prerequisites
1. **Ollama Installation**: Ollama must be running on `localhost:11434`
2. **Available Models**: At least one model should be installed (e.g., `ollama pull llama3.2`)
3. **MongoDB**: For chat history storage
4. **Laravel HTTP Client**: For API communication

### Installation Steps
1. **Install Ollama**: Download from [ollama.ai](https://ollama.ai)
2. **Pull Models**:
   ```bash
   ollama pull llama3.2
   ollama pull deepseek-r1:1.5b
   ```
3. **Start Ollama**: `ollama serve`
4. **Verify Connection**: Check `http://localhost:11434/api/tags`

## Performance Considerations

### Optimization Features
- **Request Timeouts**: 60-second timeout for AI responses
- **Model Caching**: Available models cached client-side
- **Efficient Queries**: Optimized MongoDB queries for chat history
- **Connection Pooling**: Reuses HTTP connections to Ollama

### Resource Management
- **Memory Usage**: Models run on local machine (no cloud costs)
- **Response Times**: Varies by model size (1.5B faster than 7B models)
- **Storage**: Chat history stored efficiently in MongoDB
- **Bandwidth**: Local API calls (no external bandwidth usage)

## Security Features

### Data Protection
- **User Isolation**: Each user's chat history is private
- **Local Processing**: All AI processing happens locally
- **No External APIs**: No data sent to external services
- **Session Security**: Standard Laravel authentication and sessions

### Input Validation
- **Message Validation**: Required, max 1000 characters
- **Model Validation**: Only allows available models
- **CSRF Protection**: All forms protected with CSRF tokens
- **XSS Prevention**: All output properly escaped

## Future Enhancements

### Planned Features
- **Voice Input**: Speech-to-text integration
- **Document Upload**: Analyze medical documents
- **Health Tracking Integration**: Connect with sensor data
- **Export Conversations**: PDF/email conversation exports
- **Multi-language Support**: Localized medical assistance

### Model Improvements
- **Fine-tuned Models**: Custom medical models
- **Streaming Responses**: Real-time response streaming
- **Context Memory**: Extended conversation context
- **Specialized Models**: Domain-specific medical models

## Troubleshooting

### Common Issues

**"Ollama Offline" Status**
- Verify Ollama is running: `ollama serve`
- Check port availability: `curl localhost:11434/api/tags`
- Restart Ollama service if needed

**Slow Response Times**
- Use smaller models (1.5B instead of 7B)
- Ensure sufficient RAM allocation
- Check system resources during inference

**Connection Errors**
- Verify firewall settings
- Check localhost connectivity
- Review Laravel logs for details

### Debug Commands
```bash
# Check Ollama status
curl localhost:11434/api/tags

# Test AI generation
curl -X POST localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{"model": "llama3.2", "prompt": "Hello", "stream": false}'

# View Laravel logs
tail -f storage/logs/laravel.log
```

## Conclusion

The AI Medical Assistant Chat feature provides a sophisticated, secure, and user-friendly way for users to interact with local AI models for health-related queries. With comprehensive testing, proper safety measures, and integration with the existing MediAssist ecosystem, it represents a significant enhancement to the application's capabilities.

The feature emphasizes responsible AI use, user privacy, and medical safety while providing an engaging and helpful user experience. 