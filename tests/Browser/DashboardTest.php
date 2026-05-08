<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class DashboardTest extends DuskTestCase
{
    public function test_dashboard_loads_with_title(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertSee('Event Sourcing')
                ->assertSee('Live');
        });
    }

    public function test_stats_bar_is_visible(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertPresent('#stat-total-events')
                ->assertPresent('#stat-active-carts')
                ->assertPresent('#stat-orders-today')
                ->assertPresent('#stat-revenue-today');
        });
    }

    public function test_trigger_buttons_are_visible(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertSeeIn('[data-action="add-to-cart"]', 'Add to Cart')
                ->assertSeeIn('[data-action="checkout"]', 'Checkout')
                ->assertSeeIn('[data-action="mark-as-paid"]', 'Mark as Paid');
        });
    }

    public function test_add_to_cart_button_shows_response(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->click('[data-action="add-to-cart"]')
                ->waitFor('#feedback-panel', 5)
                ->assertVisible('#feedback-panel')
                ->assertPresent('#feedback-content');
        });
    }

    public function test_event_stream_panel_is_visible(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertPresent('#event-stream');
        });
    }
}
