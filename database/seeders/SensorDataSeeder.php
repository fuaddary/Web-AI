<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SensorData;
use App\Models\User;
use Carbon\Carbon;

class SensorDataSeeder extends Seeder
{
    public function run()
    {
        // Get the first user, create one if none exists
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $now = Carbon::now();
        
        // Generate realistic sensor data for the last 7 days
        for ($day = 6; $day >= 0; $day--) {
            $currentDay = $now->copy()->subDays($day);
            
            // Generate 24 readings per day (one per hour)
            for ($hour = 0; $hour < 24; $hour++) {
                $timestamp = $currentDay->copy()->addHours($hour);
                
                // Generate realistic temperature (18-30°C with daily cycle)
                $baseTemp = 22; // Base temperature
                $dailyCycle = sin(($hour - 6) * pi() / 12) * 4; // Daily temperature cycle
                $randomVariation = (rand(-20, 20) / 10); // ±2°C random variation
                $temperature = $baseTemp + $dailyCycle + $randomVariation;
                
                // Generate realistic humidity (30-80% with inverse correlation to temperature)
                $baseHumidity = 55; // Base humidity
                $tempEffect = -($temperature - 22) * 2; // Humidity decreases as temp increases
                $randomHumidityVariation = (rand(-100, 100) / 10); // ±10% random variation
                $humidity = max(30, min(80, $baseHumidity + $tempEffect + $randomHumidityVariation));
                
                SensorData::create([
                    'user_id' => $user->id,
                    'suhu' => round($temperature, 1),
                    'kelembapan' => round($humidity, 1),
                    'location' => 'Living Room',
                    'device_id' => 'DHT22_001',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }

        // Add some additional recent readings for the last few hours with more frequent updates
        for ($minute = 300; $minute >= 0; $minute -= 5) { // Every 5 minutes for last 5 hours
            $timestamp = $now->copy()->subMinutes($minute);
            
            // More stable recent readings
            $baseTemp = 23.5;
            $randomTempVariation = (rand(-5, 5) / 10); // ±0.5°C
            $temperature = $baseTemp + $randomTempVariation;
            
            $baseHumidity = 52;
            $randomHumidityVariation = (rand(-20, 20) / 10); // ±2%
            $humidity = $baseHumidity + $randomHumidityVariation;
            
            SensorData::create([
                'user_id' => $user->id,
                'suhu' => round($temperature, 1),
                'kelembapan' => round($humidity, 1),
                'location' => 'Living Room',
                'device_id' => 'DHT22_001',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        $this->command->info('Created ' . SensorData::count() . ' sensor data records.');
    }
} 