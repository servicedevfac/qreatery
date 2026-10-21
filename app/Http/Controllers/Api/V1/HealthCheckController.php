<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $isHealthy = collect($checks)->every(fn (array $check): bool => $check['status'] === 'ok');

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'environment' => config('app.env'),
            'checks' => $checks,
        ], $isHealthy ? 200 : 503);
    }

    /**
     * @return array{status: string, message?: string, latency_ms?: float}
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::select('SELECT 1');

            return [
                'status' => 'ok',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (Throwable $e) {
            return $this->failure($e, 'Base de données indisponible.');
        }
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkCache(): array
    {
        try {
            $key = 'healthcheck_'.uniqid();
            Cache::put($key, '1', 10);
            $val = Cache::get($key);
            Cache::forget($key);

            if ($val !== '1') {
                return ['status' => 'error', 'message' => 'Cache value mismatch'];
            }

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return $this->failure($e, 'Service de cache indisponible.');
        }
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkStorage(): array
    {
        try {
            $filename = 'healthcheck_'.uniqid().'.txt';
            Storage::disk('local')->put($filename, 'test');
            Storage::disk('local')->delete($filename);

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return $this->failure($e, 'Stockage indisponible.');
        }
    }

    /** @return array{status: string, message: string} */
    private function failure(Throwable $exception, string $publicMessage): array
    {
        report($exception);

        return [
            'status' => 'error',
            'message' => config('app.debug') ? $exception->getMessage() : $publicMessage,
        ];
    }
}
