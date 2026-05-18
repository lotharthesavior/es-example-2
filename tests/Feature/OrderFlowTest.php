<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Models\Order;
use App\Models\Product;
use Tests\TestCase;

final class OrderFlowTest extends TestCase
{
    private function createTestProduct(): Product
    {
        return Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'price_in_cents' => 9999,
            'description' => 'A test product',
            'active' => true,
        ]);
    }

    public function test_can_add_to_cart(): void
    {
        $product = $this->createTestProduct();

        $response = $this->postJson('/demo/add-to-cart', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['uuid', 'cart_uuid', 'status', 'items', 'totalInCents', 'totalFormatted', 'itemCount']);

        $this->assertNotNull($response->json('cart_uuid'));
    }

    public function test_can_checkout_after_adding_to_cart(): void
    {
        $product = $this->createTestProduct();

        $addResponse = $this->postJson('/demo/add-to-cart', [
            'product_id' => $product->id,
        ]);
        $cartUuid = $addResponse->json('cart_uuid');
        $this->assertNotNull($cartUuid, 'Cart UUID should not be null from add-to-cart');

        $checkoutResponse = $this->postJson('/demo/checkout', [
            'cart_uuid' => $cartUuid,
        ]);

        $checkoutResponse->assertStatus(200);
        $checkoutData = $checkoutResponse->json();
        $this->assertNotNull($checkoutData, 'Checkout response JSON should not be null');
        $this->assertArrayHasKey('order_uuid', $checkoutData, 'Checkout should return order_uuid: '.json_encode($checkoutData));

        $orderUuid = $checkoutData['order_uuid'];
        $this->assertNotNull($orderUuid, 'Order UUID should not be null');

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertNotNull($order, 'Order should exist in database');
        $this->assertSame(OrderStatus::Placed, $order->status);
    }

    public function test_full_order_lifecycle(): void
    {
        $product = $this->createTestProduct();

        // 1. Add to cart
        $addResponse = $this->postJson('/demo/add-to-cart', [
            'product_id' => $product->id,
        ]);
        $cartUuid = $addResponse->json('cart_uuid');

        // 2. Checkout
        $checkoutResponse = $this->postJson('/demo/checkout', [
            'cart_uuid' => $cartUuid,
        ]);
        $orderUuid = $checkoutResponse->json('order_uuid');

        // 3. Mark as paid
        $paidResponse = $this->postJson('/demo/mark-as-paid', [
            'order_uuid' => $orderUuid,
        ]);
        $paidResponse->assertStatus(200);

        // 4. Mark as shipped
        $shippedResponse = $this->postJson('/demo/mark-as-shipped', [
            'order_uuid' => $orderUuid,
        ]);
        $shippedResponse->assertStatus(200);

        // 5. Mark as delivered
        $deliveredResponse = $this->postJson('/demo/mark-as-delivered', [
            'order_uuid' => $orderUuid,
        ]);
        $deliveredResponse->assertStatus(200);

        // Verify final state
        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->shipped_at);
        $this->assertNotNull($order->delivered_at);
    }

    public function test_can_cancel_order(): void
    {
        $product = $this->createTestProduct();

        $addResponse = $this->postJson('/demo/add-to-cart', ['product_id' => $product->id]);
        $cartUuid = $addResponse->json('cart_uuid');

        $checkoutResponse = $this->postJson('/demo/checkout', ['cart_uuid' => $cartUuid]);
        $orderUuid = $checkoutResponse->json('order_uuid');

        $cancelResponse = $this->postJson('/demo/cancel-order', [
            'order_uuid' => $orderUuid,
            'reason' => 'Test cancellation',
        ]);
        $cancelResponse->assertStatus(200);

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
    }

    public function test_can_refund_delivered_order(): void
    {
        $product = $this->createTestProduct();

        $addResponse = $this->postJson('/demo/add-to-cart', ['product_id' => $product->id]);
        $cartUuid = $addResponse->json('cart_uuid');

        $checkoutResponse = $this->postJson('/demo/checkout', ['cart_uuid' => $cartUuid]);
        $orderUuid = $checkoutResponse->json('order_uuid');

        $this->postJson('/demo/mark-as-paid', ['order_uuid' => $orderUuid]);
        $this->postJson('/demo/mark-as-shipped', ['order_uuid' => $orderUuid]);
        $this->postJson('/demo/mark-as-delivered', ['order_uuid' => $orderUuid]);

        $refundResponse = $this->postJson('/demo/refund-order', [
            'order_uuid' => $orderUuid,
            'amount_in_cents' => 9999,
            'reason' => 'Not satisfied',
        ]);
        $refundResponse->assertStatus(200);

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Refunded, $order->status);
    }

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200)
            ->assertSee('Event Sourcing');
    }

    public function test_stats_endpoint_returns_json(): void
    {
        $response = $this->getJson('/api/stats');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'totalEvents',
                'activeCarts',
                'ordersToday',
                'revenueToday',
            ]);
    }

    public function test_remove_from_cart(): void
    {
        $product = $this->createTestProduct();

        $addResponse = $this->postJson('/demo/add-to-cart', ['product_id' => $product->id]);
        $cartUuid = $addResponse->json('cart_uuid');

        $removeResponse = $this->postJson('/demo/remove-from-cart', [
            'cart_uuid' => $cartUuid,
            'product_id' => (string) $product->id,
        ]);
        $removeResponse->assertStatus(200);

        /** @var Cart $cart */
        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        $items = is_array($cart->items) ? $cart->items : [];
        $this->assertArrayNotHasKey((string) $product->id, $items);
    }
}
