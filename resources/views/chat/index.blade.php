<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('AI Medical Assistant') }}
            </h2>
            <div class="flex items-center space-x-4">
                <!-- Ollama Status -->
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full {{ $ollamaStatus ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <span class="text-sm text-gray-600">
                        {{ $ollamaStatus ? 'Ollama Connected' : 'Ollama Offline' }}
                    </span>
                </div>
                <!-- Model Selector -->
                <select id="modelSelector" class="text-sm border-gray-300 rounded-md shadow-sm">
                    <option value="llama3.2">llama3.2</option>
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Chat Container -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                <!-- Chat Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-white">Medical AI Assistant</h3>
                        <button id="clearHistory" class="bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded-lg text-sm transition duration-200">
                            Clear History
                        </button>
                    </div>
                    <p class="text-blue-100 text-sm mt-1">Ask me about symptoms, health advice, or general medical questions</p>
                </div>

                <!-- Chat Messages -->
                <div id="chatMessages" class="h-96 overflow-y-auto p-6 space-y-4">
                    @if($chatHistory->count() > 0)
                        @foreach($chatHistory as $chat)
                            <!-- User Message -->
                            <div class="flex justify-end">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 bg-blue-600 text-white rounded-lg">
                                    <p class="text-sm">{{ $chat->message }}</p>
                                    <p class="text-xs text-blue-200 mt-1">{{ $chat->created_at->format('M j, H:i') }}</p>
                                </div>
                            </div>
                            
                            <!-- AI Response -->
                            <div class="flex justify-start">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 bg-gray-100 text-gray-800 rounded-lg">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <div class="w-6 h-6 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">MediAssist</span>
                                        <span class="text-xs text-gray-400">({{ $chat->model }})</span>
                                    </div>
                                    <p class="text-sm whitespace-pre-wrap">{{ $chat->response }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $chat->created_at->format('M j, H:i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to MediAssist!</h3>
                            <p class="text-gray-600 text-sm max-w-md mx-auto">
                                I'm your AI medical assistant. Ask me about health symptoms, general medical advice, or wellness tips. 
                                Remember, I provide general information only and cannot replace professional medical consultation.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Typing Indicator -->
                <div id="typingIndicator" class="hidden px-6 py-2">
                    <div class="flex justify-start">
                        <div class="max-w-xs lg:max-w-md px-4 py-2 bg-gray-100 text-gray-800 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-600">MediAssist is typing...</span>
                            </div>
                            <div class="flex space-x-1 mt-2">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Input -->
                <div class="border-t border-gray-100 p-6">
                    <form id="chatForm" class="flex space-x-4">
                        <div class="flex-1">
                            <textarea 
                                id="messageInput" 
                                rows="2" 
                                class="w-full border-gray-300 rounded-lg shadow-sm resize-none focus:border-blue-500 focus:ring-blue-500" 
                                placeholder="Ask me about your health concerns..."
                                maxlength="1000"
                            ></textarea>
                        </div>
                        <button 
                            type="submit" 
                            id="sendButton"
                            class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-2 rounded-lg font-medium transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Send
                        </button>
                    </form>
                    
                    <!-- Disclaimer -->
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs text-yellow-800">
                            <strong>Disclaimer:</strong> This AI assistant provides general health information only. 
                            Always consult with healthcare professionals for medical diagnosis, treatment, or emergencies.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const chatMessages = document.getElementById('chatMessages');
            const typingIndicator = document.getElementById('typingIndicator');
            const clearHistoryBtn = document.getElementById('clearHistory');
            const modelSelector = document.getElementById('modelSelector');

            // Load available models
            loadModels();

            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });

            // Handle Enter key (Shift+Enter for new line)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    handleSendMessage();
                }
            });

            // Handle form submission
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleSendMessage();
            });

            // Clear history
            clearHistoryBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear your chat history?')) {
                    clearChatHistory();
                }
            });

            function handleSendMessage() {
                const message = messageInput.value.trim();
                if (!message || sendButton.disabled) return;

                // Add user message to chat
                addMessageToChat(message, 'user');
                
                // Clear input and disable send button
                messageInput.value = '';
                messageInput.style.height = 'auto';
                setSendingState(true);

                // Send to API
                sendMessageToAPI(message);
            }

            function sendMessageToAPI(message) {
                const model = modelSelector.value;
                
                fetch('{{ route("chat.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: message,
                        model: model
                    })
                })
                .then(response => response.json())
                .then(data => {
                    setSendingState(false);
                    
                    if (data.success) {
                        addMessageToChat(data.response, 'ai', data.model, data.timestamp);
                    } else {
                        addErrorMessage(data.error || 'Failed to get response');
                    }
                })
                .catch(error => {
                    setSendingState(false);
                    addErrorMessage('Connection error. Please check if Ollama is running.');
                });
            }

            function addMessageToChat(content, sender, model = null, timestamp = null) {
                const messageDiv = document.createElement('div');
                
                if (sender === 'user') {
                    messageDiv.innerHTML = `
                        <div class="flex justify-end">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 bg-blue-600 text-white rounded-lg">
                                <p class="text-sm">${escapeHtml(content)}</p>
                                <p class="text-xs text-blue-200 mt-1">${new Date().toLocaleString('en-US', {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})}</p>
                            </div>
                        </div>
                    `;
                } else {
                    messageDiv.innerHTML = `
                        <div class="flex justify-start">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 bg-gray-100 text-gray-800 rounded-lg">
                                <div class="flex items-center space-x-2 mb-2">
                                    <div class="w-6 h-6 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600">MediAssist</span>
                                    ${model ? `<span class="text-xs text-gray-400">(${model})</span>` : ''}
                                </div>
                                <p class="text-sm whitespace-pre-wrap">${escapeHtml(content)}</p>
                                <p class="text-xs text-gray-500 mt-1">${timestamp || new Date().toLocaleString('en-US', {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})}</p>
                            </div>
                        </div>
                    `;
                }
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function addErrorMessage(error) {
                const messageDiv = document.createElement('div');
                messageDiv.innerHTML = `
                    <div class="flex justify-start">
                        <div class="max-w-xs lg:max-w-md px-4 py-2 bg-red-100 border border-red-200 text-red-800 rounded-lg">
                            <p class="text-sm">⚠️ ${escapeHtml(error)}</p>
                        </div>
                    </div>
                `;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function setSendingState(sending) {
                sendButton.disabled = sending;
                sendButton.textContent = sending ? 'Sending...' : 'Send';
                typingIndicator.classList.toggle('hidden', !sending);
                
                if (sending) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }

            function clearChatHistory() {
                fetch('{{ route("chat.clear") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }

            function loadModels() {
                fetch('{{ route("chat.models") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.models.length > 0) {
                        modelSelector.innerHTML = '';
                        data.models.forEach(model => {
                            const option = document.createElement('option');
                            option.value = model;
                            option.textContent = model;
                            modelSelector.appendChild(option);
                        });
                    }
                });
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>
</x-app-layout> 