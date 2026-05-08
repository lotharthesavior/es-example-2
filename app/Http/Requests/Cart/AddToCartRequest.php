<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\DataTransferObjects\Cart\AddToCartData;
use Illuminate\Foundation\Http\FormRequest;

final class AddToCartRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'cart_uuid' => ['nullable', 'uuid'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDto(): AddToCartData
    {
        $v = $this->validated();

        return new AddToCartData(
            productId: isset($v['product_id']) ? (int) $v['product_id'] : null,
            cartUuid: $v['cart_uuid'] ?? null,
            quantity: (int) ($v['quantity'] ?? 1),
        );
    }
}
