<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Services\Parking\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');

        $query = NotificationLog::query()->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('notifications.index', compact('logs', 'status'));
    }

    public function processQueue(NotificationCenterService $service): RedirectResponse
    {
        $result = $service->dispatchPending(200);

        return back()->with('create', 'Notificacoes enviadas: ' . (int) ($result['sent'] ?? 0));
    }
}
