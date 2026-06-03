<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Resources\TimeSlotConfigResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TimeSlotConfigController extends Controller
{
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        $this->authorize('view', $restaurant);

        $configs = $restaurant->timeSlotConfigs()->orderBy('day_of_week')->get();

        // Fill missing days with defaults so client always gets 7 rows
        $existing = $configs->keyBy('day_of_week');
        $all = collect(range(0, 6))->map(function (int $day) use ($existing, $restaurant) {
            if ($existing->has($day)) {
                return $existing[$day];
            }

            return (object) [
                'id' => null,
                'restaurant_id' => $restaurant->id,
                'day_of_week' => $day,
                'open_time' => null,
                'close_time' => null,
                'slot_duration' => 30,
                'booking_duration' => 0,
                'is_day_off' => true,
            ];
        });

        return TimeSlotConfigResource::collection($all);
    }

    public function bulkUpdate(Request $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('update', $restaurant);

        $request->validate([
            'configs' => ['required', 'array', 'min:7', 'max:7'],
            'configs.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'configs.*.is_day_off' => ['required', 'boolean'],
            'configs.*.open_time' => ['nullable', 'date_format:H:i'],
            'configs.*.close_time' => ['nullable', 'date_format:H:i', 'after:configs.*.open_time'],
            'configs.*.slot_duration' => ['nullable', 'integer', 'min:15', 'max:120'],
            'configs.*.booking_duration' => ['nullable', 'integer', 'min:0', 'max:480'],
        ]);

        foreach ($request->input('configs') as $config) {
            $restaurant->timeSlotConfigs()->updateOrCreate(
                ['day_of_week' => $config['day_of_week']],
                [
                    'is_day_off' => $config['is_day_off'],
                    'open_time' => $config['is_day_off'] ? null : ($config['open_time'] ?? null),
                    'close_time' => $config['is_day_off'] ? null : ($config['close_time'] ?? null),
                    'slot_duration' => $config['slot_duration'] ?? 30,
                    'booking_duration' => $config['booking_duration'] ?? 0,
                ]
            );
        }

        return response()->json(['message' => 'Расписание обновлено.']);
    }
}
