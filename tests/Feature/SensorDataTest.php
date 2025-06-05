<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SensorData;
use Carbon\Carbon;

class SensorDataTest extends TestCase
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

    /** @test */
    public function dashboard_can_be_accessed_by_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Environmental Dashboard');
    }

    /** @test */
    public function dashboard_shows_no_data_message_when_no_sensor_data_exists()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('No data');
    }

    /** @test */
    public function dashboard_displays_sensor_data_correctly()
    {
        // Create test sensor data
        SensorData::create([
            'user_id' => $this->user->id,
            'suhu' => 23.5,
            'kelembapan' => 45.2,
            'location' => 'Test Room',
            'device_id' => 'TEST_001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('23.5');
        $response->assertSee('45.2');
    }

    /** @test */
    public function can_create_sensor_data_via_api()
    {
        $sensorData = [
            'user_id' => $this->user->id,
            'suhu' => 25.0,
            'kelembapan' => 50.0,
            'location' => 'Living Room',
            'device_id' => 'DHT22_001',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sensor/data', $sensorData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Sensor data stored successfully'
        ]);

        $this->assertDatabaseHas('sensor_data', [
            'user_id' => $this->user->id,
            'suhu' => 25.0,
            'kelembapan' => 50.0,
        ]);
    }

    /** @test */
    public function sensor_data_api_validates_required_fields()
    {
        $invalidData = [
            'suhu' => 'invalid',
            'kelembapan' => 150, // Above max
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sensor/data', $invalidData);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error'
        ]);
    }

    /** @test */
    public function can_get_latest_sensor_readings()
    {
        // Create multiple readings
        for ($i = 0; $i < 5; $i++) {
            SensorData::create([
                'user_id' => $this->user->id,
                'suhu' => 20 + $i,
                'kelembapan' => 40 + $i,
                'location' => 'Test Room',
                'device_id' => 'TEST_001',
                'created_at' => now()->subMinutes($i),
                'updated_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->get('/api/v1/sensor/latest?user_id=' . $this->user->id . '&limit=3');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertEquals(24, $data[0]['suhu']); // Latest should be first
    }

    /** @test */
    public function can_get_sensor_statistics()
    {
        // Create test data for statistics
        $temperatures = [20, 22, 24, 26, 28];
        $humidities = [40, 45, 50, 55, 60];

        for ($i = 0; $i < 5; $i++) {
            SensorData::create([
                'user_id' => $this->user->id,
                'suhu' => $temperatures[$i],
                'kelembapan' => $humidities[$i],
                'location' => 'Test Room',
                'device_id' => 'TEST_001',
                'created_at' => now()->subHours($i),
                'updated_at' => now()->subHours($i),
            ]);
        }

        $response = $this->get('/api/v1/sensor/stats?user_id=' . $this->user->id);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $stats = $response->json('data');
        $this->assertArrayHasKey('average_temperature', $stats);
        $this->assertArrayHasKey('average_humidity', $stats);
        $this->assertArrayHasKey('total_readings', $stats);
        $this->assertEquals(5, $stats['total_readings']);
    }

    /** @test */
    public function sensor_data_model_scopes_work_correctly()
    {
        // Create old data (outside 24h window)
        SensorData::create([
            'user_id' => $this->user->id,
            'suhu' => 20,
            'kelembapan' => 40,
            'created_at' => now()->subHours(25),
            'updated_at' => now()->subHours(25),
        ]);

        // Create recent data (within 24h window)
        SensorData::create([
            'user_id' => $this->user->id,
            'suhu' => 25,
            'kelembapan' => 50,
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ]);

        $recentCount = SensorData::forUser($this->user->id)->recent(24)->count();
        $totalCount = SensorData::forUser($this->user->id)->count();

        $this->assertEquals(1, $recentCount);
        $this->assertEquals(2, $totalCount);
    }

    /** @test */
    public function sensor_data_average_calculations_work()
    {
        // Create test data with known averages
        $temperatures = [20, 22, 24, 26, 28]; // Average: 24
        $humidities = [40, 45, 50, 55, 60];   // Average: 50

        for ($i = 0; $i < 5; $i++) {
            SensorData::create([
                'user_id' => $this->user->id,
                'suhu' => $temperatures[$i],
                'kelembapan' => $humidities[$i],
                'created_at' => now()->subHours($i),
                'updated_at' => now()->subHours($i),
            ]);
        }

        $avgTemp = SensorData::getAverageTemperature($this->user->id, 24);
        $avgHumidity = SensorData::getAverageHumidity($this->user->id, 24);

        $this->assertEquals(24, $avgTemp);
        $this->assertEquals(50, $avgHumidity);
    }

    /** @test */
    public function api_key_authentication_works()
    {
        $sensorData = [
            'api_key' => $this->user->email, // Using email as simple API key
            'suhu' => 23.0,
            'kelembapan' => 45.0,
            'location' => 'API Test Room',
            'device_id' => 'API_001',
        ];

        $response = $this->postJson('/api/v1/sensor/store', $sensorData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Sensor data stored successfully'
        ]);
    }

    /** @test */
    public function api_key_authentication_fails_with_invalid_key()
    {
        $sensorData = [
            'api_key' => 'invalid@email.com',
            'suhu' => 23.0,
            'kelembapan' => 45.0,
        ];

        $response = $this->postJson('/api/v1/sensor/store', $sensorData);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid API key'
        ]);
    }

    /** @test */
    public function dashboard_chart_data_api_works()
    {
        // Create hourly test data
        for ($i = 0; $i < 24; $i++) {
            SensorData::create([
                'user_id' => $this->user->id,
                'suhu' => 20 + ($i % 10),
                'kelembapan' => 40 + ($i % 20),
                'created_at' => now()->subHours($i),
                'updated_at' => now()->subHours($i),
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get('/api/sensor-data?period=24h');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(24, $data);
    }
} 