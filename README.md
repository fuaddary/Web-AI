<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Framework">
  <h1 align="center">MediAssist</h1>
  <p align="center">Your Personal AI Health Companion</p>
</p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About MediAssist

MediAssist is a comprehensive health monitoring and AI assistance platform built with Laravel and modern web technologies. It combines health data tracking with powerful AI capabilities to provide personalized health insights while maintaining complete privacy and security.

### 🏥 Core Features

- **🤖 AI Medical Assistant**: Chat with an intelligent AI assistant powered by Ollama for health guidance and information
- **📊 Health Data Tracking**: Monitor temperature, humidity, and environmental sensors with real-time data visualization
- **🔒 Privacy-First Design**: All AI processing runs locally with Ollama - your data never leaves your control
- **📱 Responsive Interface**: Beautiful, modern UI built with Tailwind CSS and Alpine.js
- **📈 Health Analytics**: Track trends, patterns, and insights from your health data
- **🗂️ Persistent History**: Secure storage of health conversations and sensor data in MongoDB

### 🛠️ Technology Stack

**Backend:**
- **Laravel 12** - Modern PHP framework with elegant syntax
- **MongoDB** - NoSQL database for flexible health data storage
- **Ollama Integration** - Local LLM processing for AI chat functionality

**Frontend:**
- **Tailwind CSS** - Utility-first CSS framework for beautiful UI
- **Alpine.js** - Lightweight JavaScript framework for interactivity
- **Vite** - Modern frontend build tool for fast development

**Development Tools:**
- **Laravel Breeze** - Simple authentication starter kit
- **Pest PHP** - Elegant testing framework with comprehensive test coverage
- **Laravel Pint** - Code style fixer for consistent formatting

## 🚀 Quick Start

### Prerequisites

1. **PHP 8.2+** with required extensions
2. **Composer** for PHP dependency management
3. **Node.js & NPM** for frontend dependencies
4. **MongoDB** for data storage
5. **Ollama** for AI functionality ([Download here](https://ollama.ai))

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd mediassist
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure MongoDB**
   ```env
   DB_CONNECTION=mongodb
   DB_HOST=127.0.0.1
   DB_PORT=27017
   DB_DATABASE=mediassist
   ```

5. **Start Ollama and pull models**
   ```bash
   ollama serve
   ollama pull llama3.2
   ollama pull deepseek-r1:1.5b
   ```

6. **Build and start the application**
   ```bash
   npm run build
   php artisan serve
   ```

## 🤖 AI Assistant Features

### Supported Models
- **llama3.2:latest** - General purpose medical assistance
- **deepseek-r1:1.5b** - Lightweight, fast responses
- **deepseek-r1:7b** - Advanced reasoning capabilities

### Medical Capabilities
- General health information and guidance
- Symptom analysis and recommendations
- Medication information (general guidance)
- Healthy lifestyle recommendations
- Emergency situation guidance

### Safety Features
- Medical disclaimers on all interactions
- Reminders to consult healthcare professionals
- Clear boundaries about AI limitations
- Emergency contact guidance for serious concerns

## 📊 Health Monitoring

### Sensor Data Tracking
- **Temperature monitoring** (°C) with trend analysis
- **Humidity levels** (%) for environmental health
- **Location-based data** for room-specific monitoring
- **Device management** for multiple sensor deployment

### Data Insights
- Real-time sensor readings and alerts
- Historical data visualization and trends
- Average calculations for customizable time periods
- Comparative analysis between time periods

## 🧪 Testing

MediAssist includes comprehensive test coverage:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test tests/Feature/ChatTest.php
php artisan test --coverage
```

**Test Coverage:**
- ✅ Authentication and authorization
- ✅ Chat functionality and AI integration
- ✅ Sensor data collection and analysis
- ✅ Database operations and relationships
- ✅ API endpoints and validation
- ✅ Error handling and edge cases

## 🔧 Development

### Available Scripts

```bash
# Development server with hot reload
composer run dev

# Run tests with code coverage
composer run test

# Code formatting and linting
./vendor/bin/pint
```

### Project Structure

```
app/
├── Models/
│   ├── User.php          # User authentication and management
│   ├── Chat.php          # AI chat conversations
│   ├── SensorData.php    # Health sensor data
│   └── Session.php       # User sessions
├── Http/Controllers/
│   ├── ChatController.php    # AI chat functionality
│   └── DashboardController.php # Health dashboard
└── ...

resources/
├── views/
│   ├── chat/             # AI assistant interface
│   ├── dashboard/        # Health monitoring dashboard
│   └── layouts/          # Application layouts
└── ...
```

## 🛡️ Security & Privacy

### Data Protection
- **Local AI Processing**: All AI computations run on your local machine via Ollama
- **Encrypted Storage**: Health data stored securely in MongoDB with proper encryption
- **No Third-Party Sharing**: Your health data never leaves your control
- **GDPR Compliant**: Built with privacy regulations in mind

### Authentication
- Laravel Breeze authentication system
- Secure password hashing and session management
- Protected routes and API endpoints

## 📖 API Documentation

### Chat Endpoints
- `GET /chat` - Display chat interface
- `POST /chat/send` - Send message to AI assistant
- `POST /chat/clear` - Clear chat history
- `GET /chat/models` - Get available Ollama models

### Health Data Endpoints
- `GET /dashboard` - Health monitoring dashboard
- `POST /sensor-data` - Submit new sensor readings
- `GET /api/sensor-data` - Retrieve historical data

## 🤝 Contributing

We welcome contributions to MediAssist! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Run tests** (`php artisan test`)
4. **Commit changes** (`git commit -m 'Add amazing feature'`)
5. **Push to branch** (`git push origin feature/amazing-feature`)
6. **Open a Pull Request**

### Development Guidelines
- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for API changes
- Ensure all tests pass before submitting

## 📄 License

MediAssist is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

### Documentation
- **Chat Feature**: See [CHAT_FEATURE.md](CHAT_FEATURE.md) for detailed AI assistant documentation
- **Testing Results**: See [TESTING_RESULTS.md](TESTING_RESULTS.md) for comprehensive test reports

### Requirements
- **Ollama**: Must be running on `localhost:11434` for AI functionality
- **MongoDB**: Required for data persistence
- **PHP 8.2+**: With required Laravel extensions

### Getting Help
For questions, bug reports, or feature requests, please:
1. Check existing documentation
2. Review test files for implementation examples
3. Open an issue with detailed information
4. Include system information and error logs

---

<p align="center">
  <strong>MediAssist</strong> - Empowering health monitoring with AI-driven insights while keeping your data private and secure.
</p>
