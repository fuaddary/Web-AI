<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SensorController extends Controller
{
    /**
     * Store new sensor reading
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,_id',
            'suhu' => 'required|numeric|between:-50,100',
            'kelembapan' => 'required|numeric|between:0,100',
            'location' => 'nullable|string|max:255',
            'device_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sensorData = SensorData::create([
                'user_id' => $request->user_id,
                'suhu' => $request->suhu,
                'kelembapan' => $request->kelembapan,
                'location' => $request->location ?? 'Unknown',
                'device_id' => $request->device_id ?? 'Unknown',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sensor data stored successfully',
                'data' => $sensorData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store sensor reading with API key authentication
     */
    public function storeWithApiKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'suhu' => 'required|numeric|between:-50,100',
            'kelembapan' => 'required|numeric|between:0,100',
            'location' => 'nullable|string|max:255',
            'device_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by API key (you should implement a proper API key system)
        // For now, using email as simple API key
        $user = User::where('email', $request->api_key)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        try {
            $sensorData = SensorData::create([
                'user_id' => $user->id,
                'suhu' => $request->suhu,
                'kelembapan' => $request->kelembapan,
                'location' => $request->location ?? 'Unknown',
                'device_id' => $request->device_id ?? 'Unknown',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sensor data stored successfully',
                'data' => $sensorData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest sensor readings for user
     */
    public function latest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,_id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $limit = $request->limit ?? 10;
        
        $latestReadings = SensorData::forUser($request->user_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['suhu', 'kelembapan', 'location', 'device_id', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $latestReadings
        ]);
    }

    /**
     * Get sensor statistics
     */
    public function statistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,_id',
            'hours' => 'nullable|integer|min:1|max:168', // Max 1 week
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $hours = $request->hours ?? 24;
        $userId = $request->user_id;

        $stats = [
            'average_temperature' => SensorData::getAverageTemperature($userId, $hours),
            'average_humidity' => SensorData::getAverageHumidity($userId, $hours),
            'temperature_trend' => SensorData::getTemperatureTrend($userId, $hours),
            'humidity_trend' => SensorData::getHumidityTrend($userId, $hours),
            'total_readings' => SensorData::forUser($userId)->recent($hours)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
} 