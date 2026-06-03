<?php

namespace App\Domains\AI\Jobs;

use App\Domains\AI\Enums\AiRequestStatus;
use App\Domains\AI\Models\AiRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessAiRequestJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly string $aiRequestId,
    ) {}

    public function handle(): void
    {
        $aiRequest = AiRequest::find($this->aiRequestId);

        if ($aiRequest === null) {
            Log::warning('ProcessAiRequestJob: request not found', ['id' => $this->aiRequestId]);

            return;
        }

        $aiRequest->update(['status' => AiRequestStatus::Processing]);

        try {
            // TODO: РёРЅС‚РµРіСЂР°С†РёСЏ СЃ AI-РїСЂРѕРІР°Р№РґРµСЂРѕРј (OpenAI, Anthropic Рё РґСЂ.)
            // $response = app(AiProviderService::class)->process($aiRequest->prompt);
            // $aiRequest->update(['response' => $response, 'status' => AiRequestStatus::Completed]);

            $aiRequest->update(['status' => AiRequestStatus::Completed]);
        } catch (\Throwable $e) {
            $aiRequest->update([
                'status' => AiRequestStatus::Failed,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessAiRequestJob failed', [
            'ai_request_id' => $this->aiRequestId,
            'error' => $e->getMessage(),
        ]);

        AiRequest::find($this->aiRequestId)?->update(['status' => AiRequestStatus::Failed]);
    }
}
