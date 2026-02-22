<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditLogger
{
    private static ?bool $activityTableExists = null;

    public static function log(string $event, array $data = []): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        $request = app()->bound('request') ? request() : null;
        $actor = self::resolveActor($request);

        $record = [
            'event' => $event,
            'level' => (string) ($data['level'] ?? 'info'),
            'description' => $data['description'] ?? null,
            'actor_type' => $data['actor_type'] ?? $actor['type'],
            'actor_id' => $data['actor_id'] ?? $actor['id'],
            'request_method' => $data['request_method'] ?? ($request?->method()),
            'request_path' => $data['request_path'] ?? ($request?->path()),
            'route_name' => $data['route_name'] ?? ($request?->route()?->getName()),
            'url' => $data['url'] ?? ($request?->fullUrl()),
            'status_code' => isset($data['status_code']) ? (int) $data['status_code'] : null,
            'ip_address' => $data['ip_address'] ?? ($request?->ip()),
            'user_agent' => self::limit((string) ($data['user_agent'] ?? ($request?->userAgent() ?? '')), 1024),
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => isset($data['subject_id']) ? (string) $data['subject_id'] : null,
            'old_values' => self::sanitize($data['old_values'] ?? null),
            'new_values' => self::sanitize($data['new_values'] ?? null),
            'metadata' => self::sanitize($data['metadata'] ?? []),
        ];

        self::persist($record);
        self::writeChannel($record);
    }

    public static function logRequest(Request $request, Response|\Symfony\Component\HttpFoundation\Response $response, float $durationMs): void
    {
        self::log('http.request', [
            'description' => trim($request->method() . ' ' . $request->path()),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'duration_ms' => round($durationMs, 2),
                'query' => $request->query(),
                'input' => $request->except(['_token', '_method']),
                'is_livewire' => $request->is('livewire/*'),
            ],
        ]);
    }

    public static function logModelEvent(string $action, Model $model): void
    {
        if (!config('audit.log_model_events', true)) {
            return;
        }

        if ($action === 'updated') {
            $changes = $model->getChanges();
            unset($changes['updated_at'], $changes['created_at']);

            if (empty($changes)) {
                return;
            }

            $original = [];
            foreach (array_keys($changes) as $attribute) {
                $original[$attribute] = $model->getOriginal($attribute);
            }

            self::log('model.updated', [
                'description' => class_basename($model) . ' #' . $model->getKey() . ' atualizado.',
                'subject_type' => get_class($model),
                'subject_id' => (string) $model->getKey(),
                'old_values' => $original,
                'new_values' => $changes,
                'metadata' => ['table' => $model->getTable()],
            ]);

            return;
        }

        if ($action === 'created') {
            self::log('model.created', [
                'description' => class_basename($model) . ' #' . $model->getKey() . ' criado.',
                'subject_type' => get_class($model),
                'subject_id' => (string) $model->getKey(),
                'new_values' => $model->getAttributes(),
                'metadata' => ['table' => $model->getTable()],
            ]);

            return;
        }

        if ($action === 'deleted') {
            self::log('model.deleted', [
                'description' => class_basename($model) . ' #' . $model->getKey() . ' removido.',
                'subject_type' => get_class($model),
                'subject_id' => (string) $model->getKey(),
                'old_values' => $model->getOriginal(),
                'metadata' => ['table' => $model->getTable()],
            ]);
        }
    }

    private static function persist(array $record): void
    {
        if (!self::hasActivityTable()) {
            return;
        }

        try {
            ActivityLog::query()->create($record);
        } catch (Throwable $e) {
            Log::warning('Falha ao gravar activity_logs.', ['error' => $e->getMessage()]);
        }
    }

    private static function writeChannel(array $record): void
    {
        $channelName = (string) config('audit.channel', 'audit');

        try {
            Log::channel($channelName)->info($record['event'], [
                'description' => $record['description'],
                'actor_type' => $record['actor_type'],
                'actor_id' => $record['actor_id'],
                'request_method' => $record['request_method'],
                'request_path' => $record['request_path'],
                'route_name' => $record['route_name'],
                'status_code' => $record['status_code'],
                'subject_type' => $record['subject_type'],
                'subject_id' => $record['subject_id'],
                'metadata' => $record['metadata'],
            ]);
        } catch (Throwable $e) {
            Log::info($record['event'], ['fallback_channel_error' => $e->getMessage()]);
        }
    }

    private static function hasActivityTable(): bool
    {
        if (self::$activityTableExists !== null) {
            return self::$activityTableExists;
        }

        try {
            self::$activityTableExists = Schema::hasTable('activity_logs');
        } catch (Throwable) {
            self::$activityTableExists = false;
        }

        return self::$activityTableExists;
    }

    private static function resolveActor(?Request $request): array
    {
        $user = Auth::user();

        if ($user instanceof User) {
            return [
                'type' => User::class,
                'id' => (int) $user->getKey(),
            ];
        }

        if ($request !== null) {
            $monthly = $request->attributes->get('monthly_subscriber');
            if (is_object($monthly) && method_exists($monthly, 'getKey')) {
                return [
                    'type' => get_class($monthly),
                    'id' => (int) $monthly->getKey(),
                ];
            }

            $monthlyId = 0;
            try {
                if ($request->hasSession()) {
                    $monthlyId = (int) $request->session()->get('monthly_subscriber_id', 0);
                }
            } catch (Throwable) {
                $monthlyId = 0;
            }

            if ($monthlyId > 0) {
                return [
                    'type' => 'monthly_subscriber',
                    'id' => $monthlyId,
                ];
            }
        }

        return ['type' => null, 'id' => null];
    }

    private static function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            $maxItems = (int) config('audit.max_array_items', 80);
            $result = [];
            $index = 0;

            foreach ($value as $key => $item) {
                if ($index >= $maxItems) {
                    $result['__truncated__'] = 'array_limit_reached';
                    break;
                }

                $result[$key] = self::isSensitiveKey((string) $key)
                    ? '***'
                    : self::sanitize($item);

                $index++;
            }

            return $result;
        }

        if (is_object($value)) {
            if ($value instanceof Model) {
                return [
                    'model' => get_class($value),
                    'id' => $value->getKey(),
                ];
            }

            if (method_exists($value, '__toString')) {
                return self::limit((string) $value, (int) config('audit.max_value_length', 2000));
            }

            return ['object' => get_class($value)];
        }

        if (is_string($value)) {
            return self::limit($value, (int) config('audit.max_value_length', 2000));
        }

        return $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(trim($key));
        $sensitiveKeys = (array) config('audit.sensitive_keys', []);

        foreach ($sensitiveKeys as $sensitiveKey) {
            $needle = strtolower((string) $sensitiveKey);
            if ($needle !== '' && str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function limit(string $value, int $max): string
    {
        if ($max <= 0 || strlen($value) <= $max) {
            return $value;
        }

        return substr($value, 0, $max) . '...';
    }
}
