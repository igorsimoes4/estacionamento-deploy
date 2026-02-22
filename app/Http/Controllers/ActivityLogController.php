<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        [$filters, $baseQuery] = $this->resolveFiltersAndQuery($request);

        /** @var LengthAwarePaginator $logs */
        $logs = (clone $baseQuery)
            ->orderByDesc('id')
            ->paginate($filters['per_page'])
            ->onEachSide(2)
            ->withQueryString();

        $metrics = [
            'total' => ActivityLog::query()->count(),
            'today' => ActivityLog::query()->whereDate('created_at', today())->count(),
            'errors' => ActivityLog::query()
                ->whereIn('level', ['error', 'critical', 'alert', 'emergency'])
                ->orWhere('status_code', '>=', 400)
                ->count(),
            'model_changes' => ActivityLog::query()
                ->whereIn('event', ['model.created', 'model.updated', 'model.deleted'])
                ->count(),
        ];

        return view('activity_logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'metrics' => $metrics,
            'pageInfo' => [
                'from' => $logs->firstItem() ?? 0,
                'to' => $logs->lastItem() ?? 0,
                'total' => $logs->total(),
                'current' => $logs->currentPage(),
                'last' => $logs->lastPage(),
            ],
            'levels' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$filters, $baseQuery] = $this->resolveFiltersAndQuery($request);

        $rows = (clone $baseQuery)
            ->orderByDesc('id')
            ->limit(10000)
            ->get();

        $filename = 'auditoria-' . Carbon::now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, [
                'ID',
                'Evento',
                'Nivel',
                'Descricao',
                'Ator Tipo',
                'Ator ID',
                'Metodo',
                'Caminho',
                'Status',
                'Assunto Tipo',
                'Assunto ID',
                'Criado em',
            ], ';');

            foreach ($rows as $log) {
                fputcsv($output, [
                    $log->id,
                    $log->event,
                    $log->level,
                    $this->truncate((string) ($log->description ?? ''), 260),
                    $log->actor_type,
                    $log->actor_id,
                    $log->request_method,
                    $log->request_path,
                    $log->status_code,
                    $log->subject_type,
                    $log->subject_id,
                    optional($log->created_at)->format('d/m/Y H:i:s'),
                ], ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$filters, $baseQuery] = $this->resolveFiltersAndQuery($request);

        /** @var Collection<int, ActivityLog> $logs */
        $logs = (clone $baseQuery)
            ->orderByDesc('id')
            ->limit(800)
            ->get();

        $pdf = PDF::loadView('activity_logs.pdf', [
            'logs' => $logs,
            'filters' => $filters,
            'generatedAt' => Carbon::now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('auditoria-' . Carbon::now()->format('Ymd-His') . '.pdf');
    }

    private function resolveFiltersAndQuery(Request $request): array
    {
        $filters = [
            'event' => trim((string) $request->query('event', '')),
            'level' => trim((string) $request->query('level', '')),
            'path' => trim((string) $request->query('path', '')),
            'actor_id' => trim((string) $request->query('actor_id', '')),
            'status_code' => trim((string) $request->query('status_code', '')),
            'from' => trim((string) $request->query('from', '')),
            'to' => trim((string) $request->query('to', '')),
            'per_page' => (int) $request->query('per_page', 20),
        ];

        $filters['per_page'] = in_array($filters['per_page'], [10, 20, 50, 100], true)
            ? $filters['per_page']
            : 20;

        $query = ActivityLog::query()
            ->when($filters['event'] !== '', function ($q) use ($filters) {
                $q->where('event', 'like', '%' . $filters['event'] . '%');
            })
            ->when($filters['level'] !== '', function ($q) use ($filters) {
                $q->where('level', $filters['level']);
            })
            ->when($filters['path'] !== '', function ($q) use ($filters) {
                $q->where('request_path', 'like', '%' . $filters['path'] . '%');
            })
            ->when($filters['actor_id'] !== '', function ($q) use ($filters) {
                $q->where('actor_id', (int) $filters['actor_id']);
            })
            ->when($filters['status_code'] !== '', function ($q) use ($filters) {
                $q->where('status_code', (int) $filters['status_code']);
            })
            ->when($filters['from'] !== '', function ($q) use ($filters) {
                $q->whereDate('created_at', '>=', $filters['from']);
            })
            ->when($filters['to'] !== '', function ($q) use ($filters) {
                $q->whereDate('created_at', '<=', $filters['to']);
            });

        return [$filters, $query];
    }

    private function truncate(string $value, int $limit): string
    {
        if (strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit) . '...';
    }
}
