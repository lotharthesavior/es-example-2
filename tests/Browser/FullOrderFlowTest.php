<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class FullOrderFlowTest extends DuskTestCase
{
    public function test_add_to_cart_flow(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertSee('Event Sourcing')
                ->click('[data-action="add-to-cart"]')
                ->waitFor('#feedback-panel', 5)
                ->assertVisible('#feedback-panel');
        });
    }

    public function test_full_order_lifecycle_via_ui(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/');

            // Step 1: Add to cart
            $browser->click('[data-action="add-to-cart"]')
                ->waitFor('#feedback-panel', 5)
                ->assertVisible('#feedback-panel');

            // Step 2: Checkout
            $browser->click('[data-action="checkout"]')
                ->waitFor('#feedback-content', 5);

            // Step 3: Mark as paid
            $browser->click('[data-action="mark-as-paid"]')
                ->pause(500);

            // Step 4: Mark as shipped
            $browser->click('[data-action="mark-as-shipped"]')
                ->pause(500);

            // Step 5: Mark as delivered
            $browser->click('[data-action="mark-as-delivered"]')
                ->pause(500);

            // Verify feedback panel shows responses
            $browser->assertVisible('#feedback-panel');
        });
    }

    public function test_replay_button_is_accessible(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertSeeIn('[data-action="replay-events"]', 'Replay All Events');
        });
    }

    public function test_architecture_overlay_toggles(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertMissing('#arch-overlay:not(.hidden)')
                ->click('button[onclick="toggleArchitecture()"]')
                ->waitUntilMissing('#arch-overlay.hidden', 3)
                ->assertVisible('#arch-overlay')
                ->assertSee('Architecture Flow');
        });
    }
}
