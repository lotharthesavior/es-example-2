<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DataTransferObjects\Order\CancelOrderData;
use App\DataTransferObjects\Order\MarkAsDeliveredData;
use App\DataTransferObjects\Order\MarkAsPaidData;
use App\DataTransferObjects\Order\MarkAsShippedData;
use App\DataTransferObjects\Order\RefundOrderData;
use App\Domain\Order\Commands\CancelOrder;
use App\Domain\Order\Commands\MarkAsDelivered;
use App\Domain\Order\Commands\MarkAsPaid;
use App\Domain\Order\Commands\MarkAsShipped;
use App\Domain\Order\Commands\RefundOrder;
use App\Domain\Order\Enums\OrderStatus;
use App\Exceptions\Domain\DomainException;
use App\Exceptions\Domain\NoCancellableOrderException;
use App\Exceptions\Domain\NoDeliverableOrderException;
use App\Exceptions\Domain\NoPlaceableOrderException;
use App\Exceptions\Domain\NoRefundableOrderException;
use App\Exceptions\Domain\NoShippableOrderException;
use App\Models\Order;
use Illuminate\Support\Str;
use Spatie\EventSourcing\Commands\CommandBus;

final class OrderCommandRepository
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly OrderQueryRepository $query,
    ) {}

    public function markAsPaid(MarkAsPaidData $dto): Order
    {
        $orderUuid = $this->resolveUuid($dto->orderUuid, OrderStatus::Placed, new NoPlaceableOrderException);

        $this->commandBus->dispatch(new MarkAsPaid(
            orderUuid: $orderUuid,
            paymentReference: 'PAY-'.strtoupper(Str::random(8)),
        ));

        return $this->refresh($orderUuid);
    }

    public function markAsShipped(MarkAsShippedData $dto): Order
    {
        $orderUuid = $this->resolveUuid($dto->orderUuid, OrderStatus::Paid, new NoShippableOrderException);

        $this->commandBus->dispatch(new MarkAsShipped(
            orderUuid: $orderUuid,
            trackingNumber: 'TRACK-'.strtoupper(Str::random(10)),
        ));

        return $this->refresh($orderUuid);
    }

    public function markAsDelivered(MarkAsDeliveredData $dto): Order
    {
        $orderUuid = $this->resolveUuid($dto->orderUuid, OrderStatus::Shipped, new NoDeliverableOrderException);

        $this->commandBus->dispatch(new MarkAsDelivered(orderUuid: $orderUuid));

        return $this->refresh($orderUuid);
    }

    public function cancelOrder(CancelOrderData $dto): Order
    {
        $orderUuid = $this->resolveUuid($dto->orderUuid, OrderStatus::Placed, new NoCancellableOrderException);

        $this->commandBus->dispatch(new CancelOrder(
            orderUuid: $orderUuid,
            reason: $dto->reason ?? 'Customer requested cancellation',
        ));

        return $this->refresh($orderUuid);
    }

    public function refundOrder(RefundOrderData $dto): Order
    {
        $orderUuid = $this->resolveUuid($dto->orderUuid, OrderStatus::Delivered, new NoRefundableOrderException);

        $order = $this->query->findByUuid($orderUuid);
        $amount = $dto->amountInCents ?? ($order === null ? 0 : (int) $order->total_in_cents);

        $this->commandBus->dispatch(new RefundOrder(
            orderUuid: $orderUuid,
            amountInCents: $amount,
            reason: $dto->reason ?? 'Customer return request',
        ));

        return $this->refresh($orderUuid);
    }

    private function resolveUuid(?string $given, OrderStatus $fallbackStatus, DomainException $missing): string
    {
        if ($given !== null) {
            return $given;
        }

        $latest = $this->query->findLatestByStatus($fallbackStatus);
        if ($latest === null) {
            throw $missing;
        }

        return $latest->uuid;
    }

    private function refresh(string $orderUuid): Order
    {
        return $this->query->findByUuid($orderUuid)
            ?? throw new \RuntimeException('Order projection missing after dispatch');
    }
}
