<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use jeremykenedy\LaravelLogger\App\Models\Activity;

class SystemDashboardController extends Controller
{
    public function metrics(): JsonResponse
    {
        $memory = $this->memoryUsage();
        $storageTotal = @disk_total_space(base_path());
        $storageFree = @disk_free_space(base_path());
        $os = @parse_ini_file('/etc/os-release', false, INI_SCANNER_RAW) ?: [];
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : false;

        return response()->json([
            'system' => [
                'name' => $os['PRETTY_NAME'] ?? php_uname('s'),
                'kernel' => php_uname('r'),
                'architecture' => php_uname('m'),
                'runtime' => PHP_OS_FAMILY === 'Linux' ? 'Linux container' : PHP_OS_FAMILY,
            ],
            'cpu' => [
                'usage_percent' => $this->cpuUsage(),
                'cores' => $this->cpuLimit(),
                'load_1m' => is_array($load) ? round($load[0], 2) : null,
            ],
            'memory' => $memory,
            'storage' => [
                'used_bytes' => is_numeric($storageTotal) && is_numeric($storageFree) ? (int) ($storageTotal - $storageFree) : null,
                'total_bytes' => is_numeric($storageTotal) ? (int) $storageTotal : null,
                'percent' => is_numeric($storageTotal) && $storageTotal > 0 && is_numeric($storageFree)
                    ? round(($storageTotal - $storageFree) / $storageTotal * 100, 1)
                    : null,
            ],
            'updated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }

    public function logs(): JsonResponse
    {
        $path = storage_path('logs/laravel.log');
        $lines = [];

        if (is_readable($path) && ($handle = @fopen($path, 'rb')) !== false) {
            fseek($handle, 0, SEEK_END);
            $position = ftell($handle);
            $contents = '';

            while ($position > 0 && substr_count($contents, "\n") < 81 && strlen($contents) < 262144) {
                $chunkSize = min(8192, $position);
                $position -= $chunkSize;
                fseek($handle, $position);
                $contents = fread($handle, $chunkSize).$contents;
            }

            fclose($handle);

            $lines = array_slice(preg_split('/\r\n|\n|\r/', $contents) ?: [], -80);
            $lines = array_values(array_filter(array_map(function ($line) {
                $line = trim($line);
                $line = preg_replace('/(password|token|secret|authorization)(\s*["\']?\s*[:=]\s*["\']?)[^,\s"\']+/i', '$1$2[redacted]', $line);

                return mb_substr($line, 0, 1200);
            }, $lines), static fn ($line) => $line !== ''));
        }

        return response()->json([
            'lines' => $lines,
            'updated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }

    public function activities(): JsonResponse
    {
        $activities = Activity::query()
            ->latest('created_at')
            ->limit(50)
            ->get(['id', 'description', 'userType', 'userId', 'route', 'methodType', 'created_at']);
        $users = User::query()
            ->whereIn('id', $activities->pluck('userId')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        return response()->json([
            'activities' => $activities->map(function (Activity $activity) use ($users) {
                $description = (string) $activity->description;
                $route = parse_url((string) $activity->route, PHP_URL_PATH);

                return [
                    'id' => (int) $activity->id,
                    'description' => mb_substr($description, 0, 180),
                    'actor' => $users->get($activity->userId)?->name ?? $activity->userType ?? 'System',
                    'route' => is_string($route) ? mb_substr($route, 0, 180) : '',
                    'method' => strtoupper((string) $activity->methodType),
                    'type' => str_contains(strtolower($description), 'register') ? 'registration' : 'activity',
                    'created_at' => $activity->created_at?->toIso8601String(),
                ];
            })->values(),
            'updated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }

    private function cpuUsage(): ?float
    {
        $stat = @file_get_contents('/sys/fs/cgroup/cpu.stat');
        if ($stat === false || ! preg_match('/^usage_usec\s+(\d+)$/m', $stat, $matches)) {
            return null;
        }

        $now = microtime(true);
        $usage = (int) $matches[1];
        $key = 'admin.system-dashboard.cpu-sample';
        $previous = Cache::get($key);
        Cache::put($key, ['usage' => $usage, 'time' => $now], now()->addMinutes(2));

        if (! is_array($previous) || ! isset($previous['usage'], $previous['time']) || $now <= $previous['time']) {
            return null;
        }

        $elapsed = $now - $previous['time'];
        $percent = (($usage - $previous['usage']) / ($elapsed * 1000000)) / $this->cpuLimit() * 100;

        return round(min(100, max(0, $percent)), 1);
    }

    private function cpuLimit(): float
    {
        $limits = @file_get_contents('/sys/fs/cgroup/cpu.max');
        if (is_string($limits)) {
            $parts = preg_split('/\s+/', trim($limits));
            if (count($parts) === 2 && $parts[0] !== 'max' && (float) $parts[1] > 0) {
                return max(0.01, (float) $parts[0] / (float) $parts[1]);
            }
        }

        $cpuInfo = @file_get_contents('/proc/cpuinfo');
        $cores = is_string($cpuInfo) ? preg_match_all('/^processor\s*:/m', $cpuInfo) : 0;

        return max(1, (float) $cores);
    }

    private function memoryUsage(): array
    {
        $current = @file_get_contents('/sys/fs/cgroup/memory.current');
        $limit = @file_get_contents('/sys/fs/cgroup/memory.max');

        if (is_string($current) && is_string($limit) && is_numeric(trim($current)) && is_numeric(trim($limit)) && (int) $limit > 0) {
            $used = (int) $current;
            $total = (int) $limit;

            return $this->usageSummary($used, $total);
        }

        $memInfo = @file_get_contents('/proc/meminfo');
        if (is_string($memInfo)
            && preg_match('/^MemTotal:\s+(\d+) kB$/m', $memInfo, $totalMatch)
            && preg_match('/^MemAvailable:\s+(\d+) kB$/m', $memInfo, $availableMatch)) {
            $total = (int) $totalMatch[1] * 1024;
            $used = $total - (int) $availableMatch[1] * 1024;

            return $this->usageSummary($used, $total);
        }

        return $this->usageSummary(0, 0);
    }

    private function usageSummary(int $used, int $total): array
    {
        return [
            'used_bytes' => $used,
            'total_bytes' => $total,
            'percent' => $total > 0 ? round($used / $total * 100, 1) : null,
        ];
    }
}