# ES Visible — Event Sourcing Order Dashboard

A Laravel demo application showcasing **Event Sourcing** with real-time visibility into e-commerce order lifecycle events. Built for the PHPTek 2026 talk: *"Building Resilient PHP Applications with an Event-Driven Mindset."*

## What It Does

A live dashboard streams domain events as they happen — products browsed, added to cart, orders placed, fulfilled, and cancelled — giving an observable window into the event log that powers the system.

Every user interaction fires an explicit domain event. Projections rebuild read models from that event stream. The dashboard subscribes via WebSockets and renders updates in real time.

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Event Sourcing | [Spatie Laravel Event Sourcing](https://spatie.be/docs/laravel-event-sourcing) |
| Real-time | Laravel Reverb (WebSockets) + Echo |
| Frontend | Alpine.js + Tailwind CSS |
| Queue | Redis |
| Database | SQLite (dev) / MySQL (prod) |

## Domain Events

```
OrderCreated       — new order initiated
ItemAddedToCart    — product added to cart
CartAbandoned      — cart left without checkout
CheckoutStarted    — user began checkout
OrderPlaced        — order confirmed + payment
OrderFulfilled     — shipped / delivered
OrderCancelled     — cancelled by user or system
```

## Architecture

```
HTTP Request
    │
    ▼
Command Handler
    │
    ▼
Aggregate Root (Order)
    │  raises domain events
    ▼
Event Store (stored_events table)
    │
    ├─▶ Projector → orders, carts (read models)
    │
    └─▶ Broadcaster → WebSocket → Dashboard
```

Aggregates are the single source of truth. Projections are disposable — delete and replay to rebuild any read model from scratch.

## Getting Started

```bash
# Clone and install
git clone <repo> es-visible && cd es-visible
composer install
npm install

# Configure
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed          # seeds demo products + sample events

# WebSocket server
php artisan reverb:start &

# Queue worker (processes events async)
php artisan queue:work &

# Assets
npm run dev

# Serve
php artisan serve
```

Open `http://localhost:8000` — the dashboard loads live.

## Triggering Events

Use the dashboard controls to fire events manually, or hit the API:

```bash
# Add item to cart
curl -X POST /api/cart/add -d '{"product_id":1,"quantity":2}'

# Place order
curl -X POST /api/orders -d '{"cart_id":"<uuid>"}'

# Fulfil order
curl -X POST /api/orders/<uuid>/fulfil
```

## Testing

```bash
# Unit + integration (Pest)
php artisan test

# Static analysis (PHPStan level 8)
./vendor/bin/phpstan analyse

# E2E browser tests (Laravel Dusk, headless Chromium)
php artisan dusk
```

All three must pass before any PR merges.

### What the tests cover

- **Unit** — aggregates raise correct events, projectors build correct state
- **Feature** — API endpoints, event replay consistency, projector idempotency
- **E2E (Dusk)** — dashboard renders events in real time, full order flow visible end-to-end

## Key Concepts Demonstrated

**Event replay** — drop any projection table, run `php artisan event-sourcing:replay`, and the read model rebuilds exactly.

**Aggregate invariants** — an `OrderCancelled` event cannot follow `OrderFulfilled`; the aggregate enforces this before persisting.

**Temporal queries** — replay events up to a point in time to see system state at any past moment.

**Optimistic concurrency** — aggregate version numbers prevent conflicting writes.

## Project Structure

```
app/
├── Domain/Orders/
│   ├── Aggregates/OrderAggregate.php
│   ├── Events/                        # domain events
│   ├── Projectors/OrderProjector.php
│   └── Reactors/NotifyDashboardReactor.php
├── Http/Controllers/
│   ├── DashboardController.php
│   └── Api/OrderController.php
└── Models/                            # projection read models
resources/
└── js/
    └── dashboard.js                   # Alpine + Echo real-time listener
tests/
├── Feature/                           # API + projector tests
├── Unit/                              # aggregate + event tests
└── Browser/                           # Dusk E2E tests
```

## Talk Context

This repo accompanies the PHPTek 2026 session. The v2 talk builds on the Longhorn PHP 2025 version with:

- Reverb replacing Pusher (self-hosted WebSockets)
- Dusk E2E coverage of the real-time dashboard
- PHPStan level 8 across the entire domain layer
- Seeder-driven demos safe to run live on stage
