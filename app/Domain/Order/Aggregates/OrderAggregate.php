<?php

declare(strict_types=1);

namespace App\Domain\Order\Aggregates;

use App\Domain\Order\Commands\CancelOrder;
use App\Domain\Order\Commands\MarkAsDelivered;
use App\Domain\Order\Commands\MarkAsPaid;
use App\Domain\Order\Commands\MarkAsShipped;
use App\Domain\Order\Commands\PlaceOrder;
use App\Domain\Order\Commands\RefundOrder;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderDelivered;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Events\OrderRefunded;
use App\Domain\Order\Events\OrderShipped;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

final class OrderAggregate extends AggregateRoot
{
    private ?OrderStatus $status = null;

    private int $totalInCents = 0;

    public function placeOrder(PlaceOrder $command): self
    {
        if ($this->status !== null) {
            throw new \DomainException('Order has already been placed.');
        }

        $this->recordThat(new OrderPlaced(
            userId: $command->userId,
            cartUuid: $command->cartUuid,
            items: $command->items,
            totalInCents: $command->totalInCents,
        ));

        return $this;
    }

    public function markAsPaid(MarkAsPaid $command): self
    {
        $this->assertStatus(OrderStatus::Placed, 'mark as paid');

        $this->recordThat(new OrderPaid(
            paymentReference: $command->paymentReference,
        ));

        return $this;
    }

    public function markAsShipped(MarkAsShipped $command): self
    {
        $this->assertStatus(OrderStatus::Paid, 'mark as shipped');

        $this->recordThat(new OrderShipped(
            trackingNumber: $command->trackingNumber,
        ));

        return $this;
    }

    public function markAsDelivered(MarkAsDelivered $command): self
    {
        $this->assertStatus(OrderStatus::Shipped, 'mark as delivered');

        $this->recordThat(new OrderDelivered);

        return $this;
    }

    public function cancelOrder(CancelOrder $command): self
    {
        if ($this->status === null) {
            throw new \DomainException('Cannot cancel an order that has not been placed.');
        }

        if (! in_array($this->status, [OrderStatus::Placed, OrderStatus::Paid])) {
            throw new \DomainException(
                "Cannot cancel order in status: {$this->status->value}. Only placed or paid orders can be cancelled."
            );
        }

        $this->recordThat(new OrderCancelled(
            reason: $command->reason,
        ));

        return $this;
    }

    public function refundOrder(RefundOrder $command): self
    {
        $this->assertStatus(OrderStatus::Delivered, 'refund');

        $this->recordThat(new OrderRefunded(
            amountInCents: $command->amountInCents,
            reason: $command->reason,
        ));

        return $this;
    }

    protected function applyOrderPlaced(OrderPlaced $event): void
    {
        $this->status = OrderStatus::Placed;
        $this->totalInCents = $event->totalInCents;
    }

    protected function applyOrderPaid(OrderPaid $event): void
    {
        $this->status = OrderStatus::Paid;
    }

    protected function applyOrderShipped(OrderShipped $event): void
    {
        $this->status = OrderStatus::Shipped;
    }

    protected function applyOrderDelivered(OrderDelivered $event): void
    {
        $this->status = OrderStatus::Delivered;
    }

    protected function applyOrderCancelled(OrderCancelled $event): void
    {
        $this->status = OrderStatus::Cancelled;
    }

    protected function applyOrderRefunded(OrderRefunded $event): void
    {
        $this->status = OrderStatus::Refunded;
    }

    public function getStatus(): ?OrderStatus
    {
        return $this->status;
    }

    public function getTotalInCents(): int
    {
        return $this->totalInCents;
    }

    private function assertStatus(OrderStatus $expected, string $action): void
    {
        if ($this->status !== $expected) {
            $current = $this->status !== null ? $this->status->value : 'not placed';
            throw new \DomainException(
                "Cannot {$action} order in status: {$current}. Expected: {$expected->value}."
            );
        }
    }
}
