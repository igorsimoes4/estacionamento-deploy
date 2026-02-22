<?php

namespace App\Services\Parking;

use App\Models\Cars;
use App\Models\DynamicPricingRule;
use App\Models\ParkingSector;
use App\Models\ParkingSpot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DynamicPricingService
{
    public function apply(float $baseAmount, string $vehicleType, Carbon $entryAt, ?Carbon $exitAt = null): float
    {
        $exitAt ??= now();

        $occupancy = $this->currentOccupancyPercent();
        $dayOfWeek = (int) $entryAt->dayOfWeek;
        $timeWindow = $entryAt->format('H:i:s');

        $rules = DynamicPricingRule::query()->enabled()->get()->filter(function (DynamicPricingRule $rule) use ($vehicleType, $dayOfWeek, $timeWindow, $occupancy) {
            if (!empty($rule->vehicle_type) && $rule->vehicle_type !== $vehicleType) {
                return false;
            }

            if ($rule->day_of_week !== null && (int) $rule->day_of_week !== $dayOfWeek) {
                return false;
            }

            if (!empty($rule->starts_at) && !empty($rule->ends_at)) {
                $start = (string) $rule->starts_at;
                $end = (string) $rule->ends_at;

                if ($start <= $end) {
                    if ($timeWindow < $start || $timeWindow > $end) {
                        return false;
                    }
                } else {
                    $crosses = $timeWindow >= $start || $timeWindow <= $end;
                    if (!$crosses) {
                        return false;
                    }
                }
            }

            $from = (int) $rule->occupancy_from;
            $to = (int) $rule->occupancy_to;

            return $occupancy >= $from && $occupancy <= $to;
        });

        $amount = $baseAmount;

        foreach ($rules as $rule) {
            $multiplier = (float) $rule->multiplier;
            $flatAddition = ((int) $rule->flat_addition_cents) / 100;

            $amount = ($amount * $multiplier) + $flatAddition;
        }

        return round(max(0, $amount), 2);
    }

    public function currentOccupancyPercent(): int
    {
        $spotsTotal = (int) ParkingSpot::query()->count();

        if ($spotsTotal > 0) {
            $occupied = (int) ParkingSpot::query()->where('status', ParkingSpot::STATUS_OCCUPIED)->count();
            return (int) round(($occupied / $spotsTotal) * 100);
        }

        $capacity = (int) ParkingSector::query()->sum('capacity');

        if ($capacity <= 0) {
            return 0;
        }

        $occupiedCars = (int) Cars::parked()->count();
        return (int) round(($occupiedCars / $capacity) * 100);
    }

    public function sectorOccupancy(): Collection
    {
        return ParkingSector::query()
            ->withCount([
                'spots as occupied_spots_count' => fn ($query) => $query->where('status', ParkingSpot::STATUS_OCCUPIED),
                'spots as total_spots_count',
            ])
            ->get()
            ->map(function (ParkingSector $sector) {
                $total = max(1, (int) $sector->total_spots_count);
                $occupied = (int) $sector->occupied_spots_count;

                $sector->occupancy_percent = (int) round(($occupied / $total) * 100);

                return $sector;
            });
    }
}
