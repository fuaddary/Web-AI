<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Environmental Dashboard') }}
            </h2>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">Last Updated:</span>
                <span class="text-sm font-medium text-gray-700">{{ $latestReading ? $latestReading->created_at->format('M j, H:i') : 'No data' }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Current Readings Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Current Temperature -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-500">Current Temperature</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ $latestReading ? number_format($latestReading->suhu, 1) : '--' }}°C
                                    </p>
                                    @if($tempTrend !== 0)
                                        <span class="ml-2 text-sm font-medium {{ $tempTrend > 0 ? 'text-red-600' : 'text-blue-600' }}">
                                            {{ $tempTrend > 0 ? '+' : '' }}{{ number_format($tempTrend, 1) }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Humidity -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-500">Current Humidity</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ $latestReading ? number_format($latestReading->kelembapan, 1) : '--' }}%
                                    </p>
                                    @if($humidityTrend !== 0)
                                        <span class="ml-2 text-sm font-medium {{ $humidityTrend > 0 ? 'text-green-600' : 'text-orange-600' }}">
                                            {{ $humidityTrend > 0 ? '+' : '' }}{{ number_format($humidityTrend, 1) }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 24h Average Temperature -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-500">24h Avg Temp</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ $avgTemp24h ? number_format($avgTemp24h, 1) : '--' }}°C
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 24h Average Humidity -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-500">24h Avg Humidity</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ $avgHumidity24h ? number_format($avgHumidity24h, 1) : '--' }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 24 Hour Chart -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">24 Hour Trends</h3>
                            <div class="flex space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Temperature
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Humidity
                                </span>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 7 Day Chart -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">7 Day Averages</h3>
                            <div class="flex space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Temperature
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Humidity
                                </span>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="weeklyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics and Health Insights -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Temperature Statistics -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Temperature Stats (24h)</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Minimum</span>
                                <span class="text-sm font-medium text-blue-600">{{ $tempStats['min'] ? number_format($tempStats['min'], 1) : '--' }}°C</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Average</span>
                                <span class="text-sm font-medium text-gray-900">{{ $tempStats['avg'] ? number_format($tempStats['avg'], 1) : '--' }}°C</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Maximum</span>
                                <span class="text-sm font-medium text-red-600">{{ $tempStats['max'] ? number_format($tempStats['max'], 1) : '--' }}°C</span>
                            </div>
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-600">
                                    @if($tempStats['avg'])
                                        @if($tempStats['avg'] >= 20 && $tempStats['avg'] <= 25)
                                            ✅ Optimal temperature range for comfort
                                        @elseif($tempStats['avg'] < 20)
                                            🧊 Temperature is below optimal range
                                        @else
                                            🔥 Temperature is above optimal range
                                        @endif
                                    @else
                                        📊 No temperature data available
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Humidity Statistics -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Humidity Stats (24h)</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Minimum</span>
                                <span class="text-sm font-medium text-orange-600">{{ $humidityStats['min'] ? number_format($humidityStats['min'], 1) : '--' }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Average</span>
                                <span class="text-sm font-medium text-gray-900">{{ $humidityStats['avg'] ? number_format($humidityStats['avg'], 1) : '--' }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Maximum</span>
                                <span class="text-sm font-medium text-green-600">{{ $humidityStats['max'] ? number_format($humidityStats['max'], 1) : '--' }}%</span>
                            </div>
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-600">
                                    @if($humidityStats['avg'])
                                        @if($humidityStats['avg'] >= 40 && $humidityStats['avg'] <= 60)
                                            ✅ Optimal humidity range for health
                                        @elseif($humidityStats['avg'] < 40)
                                            🏜️ Humidity is below optimal range
                                        @else
                                            💧 Humidity is above optimal range
                                        @endif
                                    @else
                                        📊 No humidity data available
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">System Status</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Total Readings</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($totalReadings) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Sensor Status</span>
                                <span class="text-sm font-medium {{ $latestReading && $latestReading->created_at->gt(now()->subMinutes(10)) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $latestReading && $latestReading->created_at->gt(now()->subMinutes(10)) ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Last Reading</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $latestReading ? $latestReading->created_at->diffForHumans() : 'Never' }}
                                </span>
                            </div>
                            @if($latestReading && !$latestReading->created_at->gt(now()->subMinutes(10)))
                                <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200">
                                    <p class="text-xs text-red-600">
                                        ⚠️ Sensor appears to be offline. Last reading was {{ $latestReading->created_at->diffForHumans() }}.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Hourly Chart Data
        const hourlyData = @json($hourlyData);
        const weeklyData = @json($weeklyData);

        // Hourly Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: hourlyData.map(d => d.hour),
                datasets: [{
                    label: 'Temperature (°C)',
                    data: hourlyData.map(d => d.temperature),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    yAxisID: 'y',
                    tension: 0.3
                }, {
                    label: 'Humidity (%)',
                    data: hourlyData.map(d => d.humidity),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Temperature (°C)'
                        },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Humidity (%)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Weekly Chart
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: weeklyData.map(d => d.day),
                datasets: [{
                    label: 'Avg Temperature (°C)',
                    data: weeklyData.map(d => d.temperature),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    yAxisID: 'y',
                }, {
                    label: 'Avg Humidity (%)',
                    data: weeklyData.map(d => d.humidity),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Temperature (°C)'
                        },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Humidity (%)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>
</x-app-layout>
