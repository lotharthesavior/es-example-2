<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DataTransferObjects\EventStore\RewindToData;
use Illuminate\Foundation\Http\FormRequest;

final class RewindToRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'timestamp' => ['required', 'date'],
            'order_uuid' => ['required', 'uuid'],
        ];
    }

    public function toDto(): RewindToData
    {
        return new RewindToData(
            orderUuid: $this->validated('order_uuid'),
            timestamp: $this->validated('timestamp'),
        );
    }
}
