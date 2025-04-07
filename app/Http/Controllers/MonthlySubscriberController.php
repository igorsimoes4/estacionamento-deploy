<?php

namespace App\Http\Controllers;

use App\Models\MonthlySubscriber;
use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\VehiclePriceUpdated;

class MonthlySubscriberController extends Controller
{
    public function index()
    {
        $subscribers = MonthlySubscriber::orderBy('name')->get();
        return view('monthly_subscribers.index', compact('subscribers'));
    }

    public function create()
    {
        return view('monthly_subscribers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(MonthlySubscriber::rules());

        try {
            DB::beginTransaction();
            
            MonthlySubscriber::create($validated);
            
            DB::commit();
            return redirect()->route('monthly-subscribers.index')
                ->with('success', 'Mensalista cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cadastrar mensalista: ' . $e->getMessage());
        }
    }

    public function edit(MonthlySubscriber $monthlySubscriber)
    {
        return view('monthly_subscribers.edit', compact('monthlySubscriber'));
    }

    public function update(Request $request, MonthlySubscriber $monthlySubscriber)
    {
        $rules = MonthlySubscriber::rules();
        $rules['cpf'] = 'required|string|unique:monthly_subscribers,cpf,' . $monthlySubscriber->id;
        $rules['vehicle_plate'] = 'required|string|unique:monthly_subscribers,vehicle_plate,' . $monthlySubscriber->id;

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();
            
            $monthlySubscriber->update($validated);
            
            DB::commit();
            return redirect()->route('monthly-subscribers.index')
                ->with('success', 'Mensalista atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar mensalista: ' . $e->getMessage());
        }
    }

    public function destroy(MonthlySubscriber $monthlySubscriber)
    {
        try {
            DB::beginTransaction();
            
            $monthlySubscriber->delete();
            
            DB::commit();
            return redirect()->route('monthly-subscribers.index')
                ->with('success', 'Mensalista removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao remover mensalista: ' . $e->getMessage());
        }
    }

    public function getVehiclePrice($type)
    {
        try {
            \Log::info('Buscando preço para tipo:', ['type' => $type]);
            $price = 0;
            
            switch ($type) {
                case 'carro':
                    $priceModel = PriceCar::first();
                    \Log::info('Modelo de preço carro:', ['model' => $priceModel]);
                    $price = $priceModel ? $priceModel->taxaMensal : 0;
                    \Log::info('Preço carro:', ['price' => $price]);
                    break;
                case 'moto':
                    $priceModel = PriceMotorcycle::first();
                    \Log::info('Modelo de preço moto:', ['model' => $priceModel]);
                    $price = $priceModel ? $priceModel->taxaMensal : 0;
                    \Log::info('Preço moto:', ['price' => $price]);
                    break;
                case 'caminhonete':
                    $priceModel = PriceTruck::first();
                    \Log::info('Modelo de preço caminhonete:', ['model' => $priceModel]);
                    $price = $priceModel ? $priceModel->taxaMensal : 0;
                    \Log::info('Preço caminhonete:', ['price' => $price]);
                    break;
            }
            
            \Log::info('Preço encontrado:', ['price' => $price]);
            
            // Disparar evento WebSocket
            broadcast(new VehiclePriceUpdated($price, $type))->toOthers();
            
            return response()->json(['price' => (float)$price]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar preço:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 