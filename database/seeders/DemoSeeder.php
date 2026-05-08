<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Cart\Aggregates\CartAggregate;
use App\Domain\Cart\Commands\AddItemToCart;
use App\Domain\Cart\Commands\StartCheckout;
use App\Domain\Order\Aggregates\OrderAggregate;
use App\Domain\Order\Commands\CancelOrder;
use App\Domain\Order\Commands\MarkAsDelivered;
use App\Domain\Order\Commands\MarkAsPaid;
use App\Domain\Order\Commands\MarkAsShipped;
use App\Domain\Order\Commands\PlaceOrder;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProducts();
        $this->seedCompletedOrders();
        $this->seedAbandonedCarts();
    }

    private function seedProducts(): void
    {
        $products = [
            [
                'name' => 'Wireless Mechanical Keyboard',
                'slug' => 'wireless-mechanical-keyboard',
                'price_in_cents' => 12999,
                'description' => 'Premium wireless mechanical keyboard with RGB backlighting and 100-hour battery life.',
            ],
            [
                'name' => 'Ultra-wide Monitor 34"',
                'slug' => 'ultra-wide-monitor-34',
                'price_in_cents' => 79900,
                'description' => '34-inch curved ultra-wide monitor with 144Hz refresh rate and HDR support.',
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'slug' => 'ergonomic-office-chair',
                'price_in_cents' => 45000,
                'description' => 'Fully adjustable ergonomic chair with lumbar support and breathable mesh back.',
            ],
            [
                'name' => 'USB-C Docking Station',
                'slug' => 'usb-c-docking-station',
                'price_in_cents' => 18999,
                'description' => '12-in-1 USB-C hub with dual 4K display support and 100W Power Delivery.',
            ],
            [
                'name' => 'Noise-Cancelling Headphones',
                'slug' => 'noise-cancelling-headphones',
                'price_in_cents' => 29999,
                'description' => 'Industry-leading noise cancellation with 30-hour battery life and premium audio quality.',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['slug' => $product['slug']], $product);
        }
    }

    private function seedCompletedOrders(): void
    {
        $products = Product::all();

        // Order 1: Full lifecycle — placed → paid → shipped → delivered
        $this->createFullLifecycleOrder($products->random(2)->values(), 'user-alice');

        // Order 2: Paid but not yet shipped
        $this->createPaidOrder($products->random(1)->values(), 'user-bob');

        // Order 3: Cancelled order
        $this->createCancelledOrder($products->random(1)->values(), 'user-charlie');
    }

    /** @param Collection<int, Product> $products */
    private function createFullLifecycleOrder(Collection $products, string $userId): void
    {
        $cartUuid = Str::uuid()->toString();
        $orderUuid = Str::uuid()->toString();

        $cartAggregate = CartAggregate::retrieve($cartUuid);

        foreach ($products as $product) {
            $cartAggregate->addItem(new AddItemToCart(
                cartUuid: $cartUuid,
                productId: (string) $product->id,
                productName: $product->name,
                quantity: 1,
                priceInCents: $product->price_in_cents,
            ));
        }

        $cartAggregate->startCheckout(new StartCheckout(
            cartUuid: $cartUuid,
            userId: $userId,
        ))->persist();

        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        /** @var array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> $items */
        $items = $cart instanceof Cart ? (is_array($cart->items) ? $cart->items : []) : [];
        $total = $cart instanceof Cart ? (int) $cart->total_in_cents : 0;

        OrderAggregate::retrieve($orderUuid)
            ->placeOrder(new PlaceOrder(
                orderUuid: $orderUuid,
                userId: $userId,
                cartUuid: $cartUuid,
                items: array_values($items),
                totalInCents: $total,
            ))
            ->markAsPaid(new MarkAsPaid(
                orderUuid: $orderUuid,
                paymentReference: 'PAY-SEED-'.strtoupper(Str::random(6)),
            ))
            ->markAsShipped(new MarkAsShipped(
                orderUuid: $orderUuid,
                trackingNumber: 'TRACK-SEED-'.strtoupper(Str::random(8)),
            ))
            ->markAsDelivered(new MarkAsDelivered(
                orderUuid: $orderUuid,
            ))
            ->persist();
    }

    /** @param Collection<int, Product> $products */
    private function createPaidOrder(Collection $products, string $userId): void
    {
        $cartUuid = Str::uuid()->toString();
        $orderUuid = Str::uuid()->toString();

        $cartAggregate = CartAggregate::retrieve($cartUuid);

        foreach ($products as $product) {
            $cartAggregate->addItem(new AddItemToCart(
                cartUuid: $cartUuid,
                productId: (string) $product->id,
                productName: $product->name,
                quantity: 1,
                priceInCents: $product->price_in_cents,
            ));
        }

        $cartAggregate->startCheckout(new StartCheckout(
            cartUuid: $cartUuid,
            userId: $userId,
        ))->persist();

        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        /** @var array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> $items */
        $items = $cart instanceof Cart ? (is_array($cart->items) ? $cart->items : []) : [];
        $total = $cart instanceof Cart ? (int) $cart->total_in_cents : 0;

        OrderAggregate::retrieve($orderUuid)
            ->placeOrder(new PlaceOrder(
                orderUuid: $orderUuid,
                userId: $userId,
                cartUuid: $cartUuid,
                items: array_values($items),
                totalInCents: $total,
            ))
            ->markAsPaid(new MarkAsPaid(
                orderUuid: $orderUuid,
                paymentReference: 'PAY-SEED-'.strtoupper(Str::random(6)),
            ))
            ->persist();
    }

    /** @param Collection<int, Product> $products */
    private function createCancelledOrder(Collection $products, string $userId): void
    {
        $cartUuid = Str::uuid()->toString();
        $orderUuid = Str::uuid()->toString();

        $cartAggregate = CartAggregate::retrieve($cartUuid);

        foreach ($products as $product) {
            $cartAggregate->addItem(new AddItemToCart(
                cartUuid: $cartUuid,
                productId: (string) $product->id,
                productName: $product->name,
                quantity: 2,
                priceInCents: $product->price_in_cents,
            ));
        }

        $cartAggregate->startCheckout(new StartCheckout(
            cartUuid: $cartUuid,
            userId: $userId,
        ))->persist();

        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        /** @var array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> $items */
        $items = $cart instanceof Cart ? (is_array($cart->items) ? $cart->items : []) : [];
        $total = $cart instanceof Cart ? (int) $cart->total_in_cents : 0;

        OrderAggregate::retrieve($orderUuid)
            ->placeOrder(new PlaceOrder(
                orderUuid: $orderUuid,
                userId: $userId,
                cartUuid: $cartUuid,
                items: array_values($items),
                totalInCents: $total,
            ))
            ->cancelOrder(new CancelOrder(
                orderUuid: $orderUuid,
                reason: 'Changed my mind — found a better price',
            ))
            ->persist();
    }

    private function seedAbandonedCarts(): void
    {
        $products = Product::all();

        // Abandoned cart 1: has items but never checked out
        $cartUuid1 = Str::uuid()->toString();
        $product1 = $products->random();
        CartAggregate::retrieve($cartUuid1)
            ->addItem(new AddItemToCart(
                cartUuid: $cartUuid1,
                productId: (string) $product1->id,
                productName: $product1->name,
                quantity: 1,
                priceInCents: $product1->price_in_cents,
            ))
            ->persist();

        // Abandoned cart 2: multiple items, never checked out
        $cartUuid2 = Str::uuid()->toString();
        $selectedProducts = $products->random(2)->values();
        $cartAggregate2 = CartAggregate::retrieve($cartUuid2);
        foreach ($selectedProducts as $product) {
            $cartAggregate2->addItem(new AddItemToCart(
                cartUuid: $cartUuid2,
                productId: (string) $product->id,
                productName: $product->name,
                quantity: 1,
                priceInCents: $product->price_in_cents,
            ));
        }
        $cartAggregate2->persist();
    }
}
