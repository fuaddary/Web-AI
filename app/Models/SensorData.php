<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class SensorData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensor_data';

    protected $fillable = [
        'user_id',
        'suhu',           // Temperature in Celsius
        'kelembapan',     // Humidity in percentage
        'location',       // Room/location name
        'device_id',      // Sensor device identifier
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'suhu' => 'float',
        'kelembapan' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for recent data (last 24 hours)
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    // Scope for specific date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Scope for specific user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Get average temperature for a period
    public static function getAverageTemperature($userId, $hours = 24)
    {
        return static::forUser($userId)
            ->recent($hours)
            ->avg('suhu');
    }

    // Get average humidity for a period
    public static function getAverageHumidity($userId, $hours = 24)
    {
        return static::forUser($userId)
            ->recent($hours)
            ->avg('kelembapan');
    }

    // Get temperature trend (last vs previous period)
    public static function getTemperatureTrend($userId, $hours = 24)
    {
        $current = static::getAverageTemperature($userId, $hours);
        $previous = static::forUser($userId)
            ->whereBetween('created_at', [
                Carbon::now()->subHours($hours * 2),
                Carbon::now()->subHours($hours)
            ])
            ->avg('suhu');

        if (!$previous || $previous == 0) return 0;
        
        return (($current - $previous) / $previous) * 100;
    }

    // Get humidity trend (last vs previous period)
    public static function getHumidityTrend($userId, $hours = 24)
    {
        $current = static::getAverageHumidity($userId, $hours);
        $previous = static::forUser($userId)
            ->whereBetween('created_at', [
                Carbon::now()->subHours($hours * 2),
                Carbon::now()->subHours($hours)
            ])
            ->avg('kelembapan');

        if (!$previous || $previous == 0) return 0;
        
        return (($current - $previous) / $previous) * 100;
    }
} 