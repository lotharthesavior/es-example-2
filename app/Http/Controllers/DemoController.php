<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\CheckoutRequest;
use App\Http\Requests\Cart\RemoveFromCartRequest;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\MarkAsDeliveredRequest;
use App\Http\Requests\Order\MarkAsPaidRequest;
use App\Http\Requests\Order\MarkAsShippedRequest;
use App\Http\Requests\Order\RefundOrderRequest;
use App\Http\Requests\RewindToRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\CheckoutResultResource;
use App\Http\Resources\EventHistoryResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ReplayResultResource;
use App\Repositories\CartCommandRepository;
use App\Repositories\EventStoreRepository;
use App\Repositories\OrderCommandRepository;

final class DemoController extends Controller
{
    public function __construct(
        private readonly CartCommandRepository $carts,
        private readonly OrderCommandRepository $orders,
        private readonly EventStoreRepository $eventStore,
    ) {}

    public function addToCart(AddToCartRequest $request): CartResource
    {
        return new CartResource($this->carts->addItem($request->toDto()));
    }

    public function removeFromCart(RemoveFromCartRequest $request): CartResource
    {
        return new CartResource($this->carts->removeItem($request->toDto()));
    }

    public function checkout(CheckoutRequest $request): CheckoutResultResource
    {
        return new CheckoutResultResource($this->carts->checkout($request->toDto()));
    }

    public function markAsPaid(MarkAsPaidRequest $request): OrderResource
    {
        return new OrderResource($this->orders->markAsPaid($request->toDto()));
    }

    public function markAsShipped(MarkAsShippedRequest $request): OrderResource
    {
        return new OrderResource($this->orders->markAsShipped($request->toDto()));
    }

    public function markAsDelivered(MarkAsDeliveredRequest $request): OrderResource
    {
        return new OrderResource($this->orders->markAsDelivered($request->toDto()));
    }

    public function cancelOrder(CancelOrderRequest $request): OrderResource
    {
        return new OrderResource($this->orders->cancelOrder($request->toDto()));
    }

    public function refundOrder(RefundOrderRequest $request): OrderResource
    {
        return new OrderResource($this->orders->refundOrder($request->toDto()));
    }

    public function replayEvents(): ReplayResultResource
    {
        return new ReplayResultResource($this->eventStore->replayAll());
    }

    public function rewindTo(RewindToRequest $request): EventHistoryResource
    {
        $dto = $request->toDto();

        return new EventHistoryResource(
            $this->eventStore->getHistoryForAggregateUntil($dto->orderUuid, $dto->timestamp)
        );
    }
}
