# ES Visible — Event Sourcing Order Dashboard

A Laravel demo application showcasing **Event Sourcing** with real-time visibility into e-commerce order lifecycle events. Built for the PHPTek 2026 talk: _"Building Resilient PHP Applications with an Event-Driven Mindset."_

## What It Does

A live dashboard streams domain events as they happen — items added to cart, checkout started, orders placed, paid, shipped, delivered, cancelled, and refunded — giving an observable window into the event log that powers the system.

Every user interaction fires an explicit domain event. Projections rebuild read models from that event stream. The dashboard subscribes via WebSockets and renders updates in real time.

## Tech Stack

| Layer          | Technology                                                                     |
| -------------- | ------------------------------------------------------------------------------ |
| Framework      | Laravel 13                                                                     |
| Event Sourcing | [Spatie Laravel Event Sourcing](https://spatie.be/docs/laravel-event-sourcing) |
| Real-time      | [Socket Conveyor](https://socketconveyor.com)                                  |
| Frontend       | Blade + vanilla JavaScript + Tailwind CSS                                      |
| Queue          | Database queue worker                                                          |
| Database       | SQLite by default (MySQL supported via Laravel config)                         |

## Domain Events

```
ItemAddedToCart     — product added to cart
ItemRemovedFromCart — product removed from cart
CartCleared         — cart emptied
CheckoutStarted     — user began checkout
CheckoutCancelled   — checkout was cancelled
OrderPlaced         — order created from cart contents
OrderPaid           — payment recorded
OrderShipped        — shipment recorded
OrderDelivered      — delivery recorded
OrderCancelled      — order cancelled
OrderRefunded       — refund recorded
```

## Architecture

```
HTTP Request
    │
    ▼
Command Handler
    │
    ▼
Aggregate Root (Cart / Order)
    │  raises domain events
    ▼
Event Store (stored_events table)
    │
    ├─▶ Projectors → carts, orders, order_history (read models)
    │
    └─▶ DashboardProjector → Broadcaster → WebSocket → Dashboard
```

Aggregates are the single source of truth. Projections are disposable — delete and replay to rebuild any read model from scratch.

## Getting Started

```bash
# Clone and install
git clone https://github.com/lotharthesavior/es-example-2 es-visible && cd es-visible

# Start docker containers
docker compose up -d
```

Open `http://localhost:8000`.

Notes:

- The page is served on port `8000`.
- Real-time updates depend on the Conveyor service on port `8181`.
- The repo includes `database/database.sqlite`, but if you need a fresh setup you should still run migrations and seed data explicitly.

## Triggering Events

Use the dashboard controls to fire events manually, or hit the API:

```bash
# Add item to cart
curl -X POST http://localhost:8000/demo/add-to-cart \
  -H 'Content-Type: application/json' \
  -d '{"product_id":1,"quantity":2}'

# Start checkout and place an order
curl -X POST http://localhost:8000/demo/checkout \
  -H 'Content-Type: application/json' \
  -d '{"cart_uuid":"<uuid>"}'

# Mark the order as shipped
curl -X POST http://localhost:8000/demo/mark-as-shipped \
  -H 'Content-Type: application/json' \
  -d '{"order_uuid":"<uuid>"}'
```

## Key Concepts Demonstrated

**Event replay** — clear projection tables and replay the event store to rebuild read models.

**Aggregate invariants** — invalid lifecycle transitions are rejected by the aggregates. For example, an order cannot be shipped before it has been paid, and a delivered order cannot be cancelled.

**Event history queries** — fetch an aggregate's event history up to a point in time for inspection and presentation.

**Projection rebuilds** — orders, carts, and order history are disposable read models rebuilt from stored events.

## Project Structure

```
app/
├── Domain/Cart/
│   ├── Aggregates/CartAggregate.php
│   ├── Events/                        # cart domain events
│   └── Projectors/CartProjector.php
├── Domain/Order/
│   ├── Aggregates/OrderAggregate.php
│   ├── Events/                        # order domain events
│   ├── Projectors/OrderProjector.php
│   └── Projectors/OrderHistoryProjector.php
├── Http/Controllers/
│   ├── DashboardController.php
│   └── DemoController.php
├── Projectors/
│   └── DashboardProjector.php
└── Repositories/                      # command/query orchestration
resources/
├── views/
│   └── dashboard.blade.php            # dashboard UI + real-time client logic
└── js/
    └── bootstrap.js                   # Socket Conveyor client bootstrap
tests/
├── Feature/                           # HTTP and broadcast tests
├── Unit/                              # aggregate and projector tests
└── Browser/                           # Dusk E2E tests
```
