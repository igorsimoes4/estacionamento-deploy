<?php

namespace App\Http\Middleware;

use App\Http\Controllers\MonthlySubscriberAccessController;
use App\Models\MonthlySubscriber;
use Closure;
use Illuminate\Http\Request;

class EnsureMonthlySubscriberAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $subscriberId = (int) $request->session()->get(MonthlySubscriberAccessController::SESSION_KEY, 0);

        if ($subscriberId <= 0) {
            return redirect()->route('monthly-access.login');
        }

        $subscriber = MonthlySubscriber::query()->find($subscriberId);

        if (!$subscriber || !$subscriber->access_enabled || !$subscriber->is_active) {
            $request->session()->forget(MonthlySubscriberAccessController::SESSION_KEY);
            return redirect()
                ->route('monthly-access.login')
                ->withErrors(['cpf' => 'Seu acesso esta inativo.']);
        }

        $request->attributes->set('monthly_subscriber', $subscriber);

        return $next($request);
    }
}

