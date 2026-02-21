<?php

namespace App\Http\Livewire;

use App\Models\Cars;
use App\Models\MonthlySubscriber;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render()
    {
        $cars = Cars::parked()->where('tipo_car', 'carro')->count();
        $motos = Cars::parked()->where('tipo_car', 'moto')->count();
        $caminhonetes = Cars::parked()->where('tipo_car', 'caminhonete')->count();
        $monthlyActive = MonthlySubscriber::active()->count();

        $entriesToday = Cars::whereDate('created_at', today())->count();
        $exitsToday = Cars::finished()->whereDate('saida', today())->count();
        $revenueToday = (float) Cars::finished()->whereDate('saida', today())->sum('preco');

        return view('livewire.dashboard-stats', [
            'cards' => [
                ['label' => 'Carros Ativos', 'value' => $cars, 'icon' => 'fa-car', 'class' => 'theme-info'],
                ['label' => 'Motos Ativas', 'value' => $motos, 'icon' => 'fa-motorcycle', 'class' => 'theme-warn'],
                ['label' => 'Caminhonetes Ativas', 'value' => $caminhonetes, 'icon' => 'fa-truck-pickup', 'class' => 'theme-danger'],
                ['label' => 'Mensalistas Ativos', 'value' => $monthlyActive, 'icon' => 'fa-users', 'class' => 'theme-success'],
            ],
            'entriesToday' => $entriesToday,
            'exitsToday' => $exitsToday,
            'revenueToday' => $revenueToday,
        ]);
    }
}
