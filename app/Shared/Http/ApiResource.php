<?php

namespace App\Shared\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Базовый Resource с конвертом {data, message, meta}.
 *
 * Для единичных объектов: используй make()->response() — автоматически добавит message.
 * Для коллекций: используй collection()->additional(['message' => 'OK'])->response().
 */
abstract class ApiResource extends JsonResource
{
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $data = $response->getData(true);

        if (isset($data['data']) && ! isset($data['message'])) {
            $data['message'] = 'OK';
            $response->setData($data);
        }
    }
}
