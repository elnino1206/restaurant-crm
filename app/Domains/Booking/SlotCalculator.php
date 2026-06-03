<?php

namespace App\Domains\Booking;

use App\Domains\Booking\DTO\SlotQueryDTO;
use App\Domains\Restaurant\Models\ClosedDate;
use App\Domains\Restaurant\Models\TimeSlotConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SlotCalculator
{
    /**
     * Returns available slot start times for the given query.
     *
     * @return Collection<int, Carbon>
     */
    public function calculate(SlotQueryDTO $dto): Collection
    {
        // day_of_week: 0=Monday … 6=Sunday (Carbon dayOfWeekIso: 1=Mon … 7=Sun)
        $dayOfWeek = $dto->date->dayOfWeekIso - 1;

        $config = TimeSlotConfig::withoutGlobalScopes()
            ->where('restaurant_id', $dto->restaurantId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $config || $config->is_day_off) {
            return collect();
        }

        $isClosed = ClosedDate::withoutGlobalScopes()
            ->where('restaurant_id', $dto->restaurantId)
            ->where('date', $dto->date->toDateString())
            ->exists();

        if ($isClosed) {
            return collect();
        }

        $open = Carbon::parse($config->open_time);
        $close = Carbon::parse($config->close_time);

        $slotStart = $dto->date->copy()->setTime($open->hour, $open->minute, 0);
        // durationMinutes=0 means open-ended (runs until closing); reserve at least one slot step
        $effectiveDuration = $dto->durationMinutes > 0 ? $dto->durationMinutes : $config->slot_duration;
        $lastStart = $dto->date->copy()->setTime($close->hour, $close->minute, 0)
            ->subMinutes($effectiveDuration);

        $slots = collect();

        while ($slotStart->lte($lastStart)) {
            $slots->push($slotStart->copy());
            $slotStart->addMinutes($config->slot_duration);
        }

        return $slots;
    }
}
