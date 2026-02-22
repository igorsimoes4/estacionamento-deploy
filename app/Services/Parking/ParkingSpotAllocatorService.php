<?php

namespace App\Services\Parking;

use App\Models\Cars;
use App\Models\ParkingReservation;
use App\Models\ParkingSpot;
use Illuminate\Support\Facades\DB;

class ParkingSpotAllocatorService
{
    public function reserveSpotForReservation(ParkingReservation $reservation): ?ParkingSpot
    {
        return DB::transaction(function () use ($reservation) {
            $spot = $this->findAvailableSpot($reservation->vehicle_type, $reservation->parking_sector_id);

            if (!$spot) {
                return null;
            }

            $spot->status = ParkingSpot::STATUS_RESERVED;
            $spot->current_reservation_id = $reservation->id;
            $spot->save();

            $reservation->parking_spot_id = $spot->id;
            $reservation->save();

            return $spot;
        });
    }

    public function occupySpotForCar(Cars $car, ?int $sectorId = null, ?int $preferredSpotId = null): ?ParkingSpot
    {
        return DB::transaction(function () use ($car, $sectorId, $preferredSpotId) {
            $spot = null;

            if ($preferredSpotId) {
                $spot = ParkingSpot::query()->whereKey($preferredSpotId)->lockForUpdate()->first();
                if ($spot && !in_array($spot->status, [ParkingSpot::STATUS_AVAILABLE, ParkingSpot::STATUS_RESERVED], true)) {
                    $spot = null;
                }
            }

            if (!$spot) {
                $spot = $this->findAvailableSpot($car->tipo_car, $sectorId, true);
            }

            if (!$spot) {
                return null;
            }

            $spot->status = ParkingSpot::STATUS_OCCUPIED;
            $spot->current_car_id = $car->id;
            $spot->occupied_since = now();
            $spot->save();

            $car->parking_spot_id = $spot->id;
            $car->parking_sector_id = $spot->parking_sector_id;
            $car->save();

            return $spot;
        });
    }

    public function releaseSpotByCar(Cars $car): void
    {
        if (!$car->parking_spot_id) {
            return;
        }

        $spot = ParkingSpot::query()->find($car->parking_spot_id);

        if (!$spot) {
            return;
        }

        $spot->status = ParkingSpot::STATUS_AVAILABLE;
        $spot->current_car_id = null;
        $spot->current_reservation_id = null;
        $spot->occupied_since = null;
        $spot->save();
    }

    private function findAvailableSpot(string $vehicleType, ?int $sectorId = null, bool $withLock = false): ?ParkingSpot
    {
        $query = ParkingSpot::query()
            ->where('status', ParkingSpot::STATUS_AVAILABLE)
            ->where(function ($builder) use ($vehicleType) {
                $builder->where('vehicle_type', $vehicleType)
                    ->orWhere('vehicle_type', 'geral');
            })
            ->orderBy('parking_sector_id')
            ->orderBy('code');

        if ($sectorId) {
            $query->where('parking_sector_id', $sectorId);
        }

        if ($withLock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }
}
