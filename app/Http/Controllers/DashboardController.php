<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get current readings (latest data)
        $latestReading = SensorData::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get 24-hour statistics
        $avgTemp24h = SensorData::getAverageTemperature($user->id, 24);
        $avgHumidity24h = SensorData::getAverageHumidity($user->id, 24);
        $tempTrend = SensorData::getTemperatureTrend($user->id, 24);
        $humidityTrend = SensorData::getHumidityTrend($user->id, 24);

        // Get total readings count
        $totalReadings = SensorData::forUser($user->id)->count();

        // Get hourly data for the last 24 hours for charts
        $hourlyData = $this->getHourlyChartData($user->id);

        // Get daily averages for the last 7 days
        $weeklyData = $this->getWeeklyChartData($user->id);

        // Get temperature and humidity ranges for health insights
        $tempStats = $this->getTemperatureStats($user->id);
        $humidityStats = $this->getHumidityStats($user->id);

        return view('dashboard', compact(
            'latestReading',
            'avgTemp24h',
            'avgHumidity24h',
            'tempTrend',
            'humidityTrend',
            'totalReadings',
            'hourlyData',
            'weeklyData',
            'tempStats',
            'humidityStats'
        ));
    }

    private function getHourlyChartData($userId)
    {
        $data = [];
        $now = Carbon::now();
        
        for ($i = 23; $i >= 0; $i--) {
            $hourStart = $now->copy()->subHours($i)->startOfHour();
            $hourEnd = $hourStart->copy()->endOfHour();
            
            // Get all readings for this hour
            $hourlyReadings = SensorData::forUser($userId)
                ->whereBetween('created_at', [$hourStart, $hourEnd])
                ->get(['suhu', 'kelembapan']);
            
            // Calculate averages manually since MongoDB doesn't support selectRaw with AVG
            $avgTemp = null;
            $avgHumidity = null;
            
            if ($hourlyReadings->count() > 0) {
                $avgTemp = round($hourlyReadings->avg('suhu'), 1);
                $avgHumidity = round($hourlyReadings->avg('kelembapan'), 1);
            }
            
            $data[] = [
                'hour' => $hourStart->format('H:i'),
                'temperature' => $avgTemp,
                'humidity' => $avgHumidity,
            ];
        }
        
        return $data;
    }

    private function getWeeklyChartData($userId)
    {
        $data = [];
        $now = Carbon::now();
        
        for ($i = 6; $i >= 0; $i--) {
            $dayStart = $now->copy()->subDays($i)->startOfDay();
            $dayEnd = $dayStart->copy()->endOfDay();
            
            // Get all readings for this day
            $dailyReadings = SensorData::forUser($userId)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->get(['suhu', 'kelembapan']);
            
            // Calculate averages manually
            $avgTemp = null;
            $avgHumidity = null;
            
            if ($dailyReadings->count() > 0) {
                $avgTemp = round($dailyReadings->avg('suhu'), 1);
                $avgHumidity = round($dailyReadings->avg('kelembapan'), 1);
            }
            
            $data[] = [
                'day' => $dayStart->format('M j'),
                'temperature' => $avgTemp,
                'humidity' => $avgHumidity,
            ];
        }
        
        return $data;
    }

    private function getTemperatureStats($userId)
    {
        $recentReadings = SensorData::forUser($userId)->recent(24)->get(['suhu']);
        
        if ($recentReadings->count() === 0) {
            return ['min' => null, 'max' => null, 'avg' => null];
        }
        
        return [
            'min' => round($recentReadings->min('suhu'), 1),
            'max' => round($recentReadings->max('suhu'), 1),
            'avg' => round($recentReadings->avg('suhu'), 1),
        ];
    }

    private function getHumidityStats($userId)
    {
        $recentReadings = SensorData::forUser($userId)->recent(24)->get(['kelembapan']);
        
        if ($recentReadings->count() === 0) {
            return ['min' => null, 'max' => null, 'avg' => null];
        }
        
        return [
            'min' => round($recentReadings->min('kelembapan'), 1),
            'max' => round($recentReadings->max('kelembapan'), 1),
            'avg' => round($recentReadings->avg('kelembapan'), 1),
        ];
    }

    public function getSensorData(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '24h');
        
        switch ($period) {
            case '1h':
                $hours = 1;
                break;
            case '12h':
                $hours = 12;
                break;
            case '7d':
                $hours = 24 * 7;
                break;
            default:
                $hours = 24;
        }
        
        $data = SensorData::forUser($user->id)
            ->recent($hours)
            ->orderBy('created_at', 'asc')
            ->get(['suhu', 'kelembapan', 'created_at'])
            ->map(function ($item) {
                return [
                    'time' => $item->created_at->format('H:i'),
                    'temperature' => round($item->suhu, 1),
                    'humidity' => round($item->kelembapan, 1),
                ];
            });
        
        return response()->json($data);
    }
} 