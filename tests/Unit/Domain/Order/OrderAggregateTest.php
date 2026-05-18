<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\Aggregates\OrderAggregate;
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
use Tests\TestCase;

final class OrderAggregateTest extends TestCase
{
    private string $orderUuid;

    /** @var array<int, array{productId: string, productName: string, quantity: int, priceInCents: int}> */
    private array $testItems;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderUuid = 'test-order-uuid-'.uniqid();
        $this->testItems = [
            [
                'productId' => 'product-1',
                'productName' => 'Test Product',
                'quantity' => 1,
                'priceInCents' => 9999,
            ],
        ];
    }

    private function orderPlacedEvent(): OrderPlaced
    {
        return new OrderPlaced(
            userId: 'user-1',
            cartUuid: 'cart-uuid-1',
            items: $this->testItems,
            totalInCents: 9999,
        );
    }

    public function test_can_place_order(): void
    {
        OrderAggregate::fake($this->orderUuid)
            ->given([])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->placeOrder(new PlaceOrder(
                    orderUuid: $this->orderUuid,
                    userId: 'user-1',
                    cartUuid: 'cart-uuid-1',
                    items: $this->testItems,
                    totalInCents: 9999,
                ));
            })
            ->assertRecorded([
                new OrderPlaced(
                    userId: 'user-1',
                    cartUuid: 'cart-uuid-1',
                    items: $this->testItems,
                    totalInCents: 9999,
                ),
            ]);
    }

    public function test_cannot_place_order_twice(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/already been placed/');

        OrderAggregate::fake($this->orderUuid)
            ->given([$this->orderPlacedEvent()])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->placeOrder(new PlaceOrder(
                    orderUuid: $this->orderUuid,
                    userId: 'user-1',
                    cartUuid: 'cart-uuid-1',
                    items: $this->testItems,
                    totalInCents: 9999,
                ));
            });
    }

    public function test_can_mark_as_paid(): void
    {
        OrderAggregate::fake($this->orderUuid)
            ->given([$this->orderPlacedEvent()])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->markAsPaid(new MarkAsPaid(
                    orderUuid: $this->orderUuid,
                    paymentReference: 'PAY-123',
                ));
            })
            ->assertRecorded([
                new OrderPaid(paymentReference: 'PAY-123'),
            ]);
    }

    public function test_cannot_mark_as_paid_if_not_placed(): void
    {
        $this->expectException(\DomainException::class);

        OrderAggregate::fake($this->orderUuid)
            ->given([])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->markAsPaid(new MarkAsPaid(
                    orderUuid: $this->orderUuid,
                    paymentReference: 'PAY-123',
                ));
            });
    }

    public function test_can_mark_as_shipped(): void
    {
        OrderAggregate::fake($this->orderUuid)
            ->given([
                $this->orderPlacedEvent(),
                new OrderPaid(paymentReference: 'PAY-123'),
            ])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->markAsShipped(new MarkAsShipped(
                    orderUuid: $this->orderUuid,
                    trackingNumber: 'TRACK-ABC',
                ));
            })
            ->assertRecorded([
                new OrderShipped(trackingNumber: 'TRACK-ABC'),
            ]);
    }

    public function test_cannot_mark_as_shipped_if_not_paid(): void
    {
        $this->expectException(\DomainException::class);

        OrderAggregate::fake($this->orderUuid)
            ->given([$this->orderPlacedEvent()])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->markAsShipped(new MarkAsShipped(
                    orderUuid: $this->orderUuid,
                    trackingNumber: 'TRACK-ABC',
                ));
            });
    }

    public function test_can_mark_as_delivered(): void
    {
        OrderAggregate::fake($this->orderUuid)
            ->given([
                $this->orderPlacedEvent(),
                new OrderPaid(paymentReference: 'PAY-123'),
                new OrderShipped(trackingNumber: 'TRACK-ABC'),
            ])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->markAsDelivered(new MarkAsDelivered(
                    orderUuid: $this->orderUuid,
                ));
            })
            ->assertRecorded([new OrderDelivered]);
    }

    public function test_can_cancel_placed_order(): void
    {
        OrderAggregate::fake($this->orderUuid)
            ->given([$this->orderPlacedEvent()])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->cancelOrder(new CancelOrder(
                    orderUuid: $this->orderUuid,
                    reason: 'Changed my mind',
                ));
            })
            ->assertRecorded([
                new OrderCancelled(cartUuid: 'cart-uuid-1', reason: 'Changed my mind'),
            ]);
    }

    public function test_can_cancel_paid_order(): void
    {
        OrderAggregate::fake($this->orderUuid)
            ->given([
                $this->orderPlacedEvent(),
                new OrderPaid(paymentReference: 'PAY-123'),
            ])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->cancelOrder(new CancelOrder(
                    orderUuid: $this->orderUuid,
                    reason: 'Customer requested',
                ));
            })
            ->assertRecorded([
                new OrderCancelled(cartUuid: 'cart-uuid-1', reason: 'Customer requested'),
            ]);
    }

    public function test_cannot_cancel_delivered_order(): void
    {
        $this->expectException(\DomainException::class);

        OrderAggregate::fake($this->orderUuid)
            ->given([
                $this->orderPlacedEvent(),
                new OrderPaid(paymentReference: 'PAY-123'),
                new OrderShipped(trackingNumber: 'TRACK-ABC'),
                new OrderDelivered,
            ])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->cancelOrder(new CancelOrder(
                    orderUuid: $this->orderUuid,
                    reason: 'Too late',
                ));
            });
    }

    public function test_can_refund_delivered_order(): void
    {
        OrderAggregate::fake($this->orderUuid)
            ->given([
                $this->orderPlacedEvent(),
                new OrderPaid(paymentReference: 'PAY-123'),
                new OrderShipped(trackingNumber: 'TRACK-ABC'),
                new OrderDelivered,
            ])
            ->when(function (OrderAggregate $aggregate): void {
                $aggregate->refundOrder(new RefundOrder(
                    orderUuid: $this->orderUuid,
                    amountInCents: 9999,
                    reason: 'Not satisfied',
                ));
            })
            ->assertRecorded([
                new OrderRefunded(amountInCents: 9999, reason: 'Not satisfied'),
            ]);
    }

    public function test_status_transitions_correctly(): void
    {
        $fakeAggregate = OrderAggregate::fake($this->orderUuid)
            ->given([
                $this->orderPlacedEvent(),
            ]);

        /** @var OrderAggregate $aggregate */
        $aggregate = $fakeAggregate->aggregateRoot();
        $this->assertSame(OrderStatus::Placed, $aggregate->getStatus());
    }
}
