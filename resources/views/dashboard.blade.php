<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Event Sourcing — Live | PHPTek 2026</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $conveyorConfig = [
            'protocol' => config('broadcasting.connections.conveyor.protocol'),
            'uri'      => config('broadcasting.connections.conveyor.public_host'),
            'port'     => (int) config('broadcasting.connections.conveyor.port'),
            'channel'  => 'event-stream',
        ];
    @endphp
    <script id="conveyor-config" type="application/json">{!! json_encode($conveyorConfig) !!}</script>
    <style>
        @keyframes slideInFromTop {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        @keyframes highlight-fade {
            0% { background-color: rgba(250, 204, 21, 0.25); }
            100% { background-color: transparent; }
        }
        .event-slide-in {
            animation: slideInFromTop 0.4s ease-out;
        }
        .event-highlight {
            animation: highlight-fade 2s ease-out;
        }
        .pulse-dot {
            animation: pulse-dot 1.5s ease-in-out infinite;
        }
        @keyframes btn-press {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(0.96); }
        }
        .btn-press:active {
            animation: btn-press 0.15s ease-out;
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen font-mono">

{{-- Header --}}
<header class="border-b border-gray-800 bg-gray-900/80 backdrop-blur-sm sticky top-0 z-50">
    <div class="max-w-screen-2xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-2xl font-bold text-white tracking-tight">
                <span class="text-yellow-400">⚡</span> Event Sourcing
                <span class="text-gray-400 font-normal">—</span>
                <span class="text-green-400">Live</span>
            </span>
            <span class="flex items-center gap-1.5 ml-2">
                <span class="w-2.5 h-2.5 rounded-full bg-green-400 pulse-dot inline-block"></span>
                <span class="text-xs text-gray-400">streaming</span>
            </span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs text-gray-500 hidden md:block">PHPTek 2026 — Savio Resende</span>
            <button
                onclick="toggleArchitecture()"
                class="btn-press text-xs bg-gray-800 hover:bg-gray-700 border border-gray-600 px-3 py-1.5 rounded-lg transition-all text-gray-300 hover:text-white"
            >
                Architecture
            </button>
        </div>
    </div>
</header>

{{-- Architecture Overlay --}}
<div id="arch-overlay" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-8" onclick="toggleArchitecture()">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-8 max-w-3xl w-full" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Architecture Flow</h2>
            <button onclick="toggleArchitecture()" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="font-mono text-sm">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="bg-blue-900/50 border border-blue-600 rounded-lg px-4 py-3 text-center min-w-[120px]">
                    <div class="text-blue-300 font-bold text-xs mb-1">COMMAND</div>
                    <div class="text-white">AddItemToCart</div>
                </div>
                <div class="text-yellow-400 text-xl">→</div>
                <div class="bg-purple-900/50 border border-purple-600 rounded-lg px-4 py-3 text-center min-w-[120px]">
                    <div class="text-purple-300 font-bold text-xs mb-1">AGGREGATE</div>
                    <div class="text-white">CartAggregate</div>
                </div>
                <div class="text-yellow-400 text-xl">→</div>
                <div class="bg-yellow-900/50 border border-yellow-600 rounded-lg px-4 py-3 text-center min-w-[120px]">
                    <div class="text-yellow-300 font-bold text-xs mb-1">EVENT</div>
                    <div class="text-white">ItemAddedToCart</div>
                </div>
                <div class="text-yellow-400 text-xl">→</div>
                <div class="space-y-2">
                    <div class="bg-green-900/50 border border-green-600 rounded-lg px-3 py-1.5 text-xs">
                        <span class="text-green-300 font-bold">PROJECTOR</span> → CartProjector (read model)
                    </div>
                    <div class="bg-green-900/50 border border-green-600 rounded-lg px-3 py-1.5 text-xs">
                        <span class="text-green-300 font-bold">PROJECTOR</span> → DashboardProjector (broadcast)
                    </div>
                    <div class="bg-orange-900/50 border border-orange-600 rounded-lg px-3 py-1.5 text-xs">
                        <span class="text-orange-300 font-bold">REACTOR</span> → CartAbandonmentReactor (side effect)
                    </div>
                </div>
            </div>
            <div class="mt-6 p-4 bg-gray-800 rounded-lg text-xs text-gray-400">
                <strong class="text-gray-200">Key principle:</strong> Commands mutate aggregate state and record events.
                Events are the source of truth. Projectors build read models. Reactors handle side effects.
                Everything can be <span class="text-yellow-400">replayed</span> from the event log.
            </div>
        </div>
    </div>
</div>

{{-- Stats Bar --}}
<div class="bg-gray-900 border-b border-gray-800">
    <div class="max-w-screen-2xl mx-auto px-6 py-3 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-white" id="stat-total-events">{{ $stats['totalEvents'] }}</div>
            <div class="text-xs text-gray-500 uppercase tracking-wider mt-0.5">Total Events</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-400" id="stat-active-carts">{{ $stats['activeCarts'] }}</div>
            <div class="text-xs text-gray-500 uppercase tracking-wider mt-0.5">Active Carts</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-green-400" id="stat-orders-today">{{ $stats['ordersToday'] }}</div>
            <div class="text-xs text-gray-500 uppercase tracking-wider mt-0.5">Orders Today</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-yellow-400" id="stat-revenue-today">{{ $stats['revenueToday'] }}</div>
            <div class="text-xs text-gray-500 uppercase tracking-wider mt-0.5">Revenue Today</div>
        </div>
    </div>
</div>

{{-- Main Content: 3-column control panel filling viewport --}}
<div class="max-w-screen-2xl mx-auto px-4 py-3">
    <div id="resizable-split" class="grid grid-cols-12 gap-3 h-[calc(100vh-160px)] min-h-[560px]">

        {{-- LEFT: Trigger Controls --}}
        <div id="left-panel" class="col-span-3 min-w-0 min-h-0 flex flex-col">
            <div class="bg-gray-900 border border-gray-800 rounded-xl flex flex-col h-full overflow-hidden">
                <div class="px-4 py-2.5 border-b border-gray-800 flex-none">
                    <h2 class="text-xs font-bold text-gray-300 uppercase tracking-wider">Trigger Events</h2>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" id="trigger-panel">

                    {{-- Cart actions --}}
                    <div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-1.5">🛒 Cart</div>
                        <select id="product-select" class="w-full bg-gray-800 border border-gray-700 text-gray-200 text-[11px] rounded-md px-2 py-1.5 mb-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">— Random product —</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->formattedPrice() }})</option>
                            @endforeach
                        </select>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button data-action="add-to-cart" class="btn-press bg-blue-700 hover:bg-blue-600 active:bg-blue-800 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-blue-600">
                                <span>🛒</span> Add
                            </button>
                            <button data-action="remove-from-cart" class="btn-press bg-gray-700 hover:bg-gray-600 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-gray-600">
                                <span>➖</span> Remove
                            </button>
                            <button data-action="checkout" class="btn-press col-span-2 bg-green-700 hover:bg-green-600 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-green-600">
                                <span>✅</span> Checkout
                            </button>
                        </div>
                    </div>

                    {{-- Order actions --}}
                    <div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-1.5">📦 Order Lifecycle</div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button data-action="mark-as-paid" class="btn-press bg-indigo-700 hover:bg-indigo-600 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-indigo-600">
                                <span>💳</span> Paid
                            </button>
                            <button data-action="mark-as-shipped" class="btn-press bg-purple-700 hover:bg-purple-600 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-purple-600">
                                <span>🚀</span> Shipped
                            </button>
                            <button data-action="mark-as-delivered" class="btn-press bg-teal-700 hover:bg-teal-600 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-teal-600">
                                <span>📬</span> Delivered
                            </button>
                            <button data-action="cancel-order" class="btn-press bg-red-800 hover:bg-red-700 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-red-700">
                                <span>❌</span> Cancel
                            </button>
                            <button data-action="refund-order" class="btn-press col-span-2 bg-orange-800 hover:bg-orange-700 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-orange-700">
                                <span>💰</span> Refund
                            </button>
                        </div>
                    </div>

                    {{-- Time Machine --}}
                    <div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-1.5">⏳ Time Machine</div>
                        <div class="grid grid-cols-1 gap-1.5">
                            <button data-action="replay-events" class="btn-press w-full bg-yellow-800 hover:bg-yellow-700 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-yellow-700">
                                <span>⏮</span> Replay All Events
                            </button>
                            <button data-action="start-new" class="btn-press w-full bg-slate-700 hover:bg-slate-600 text-white text-[11px] font-semibold px-2 py-2 rounded-md transition-all flex items-center justify-center gap-1 border border-slate-600">
                                <span>🆕</span> Start New Session
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resize divider (hidden, kept as no-op for any old JS refs) --}}
        <div id="split-divider" class="hidden" role="separator" aria-orientation="vertical"></div>

        {{-- CENTER: Cart + Order state, then Live Event Stream --}}
        <div class="col-span-6 min-w-0 min-h-0 flex flex-col gap-3">

            {{-- Current state strip: cart + order side by side --}}
            <div class="flex items-center justify-between gap-3 flex-none">
                <span class="text-[11px] text-gray-500 uppercase tracking-wider">Current State</span>
                <button id="refresh-state-btn" type="button" title="Reload cart + order state"
                        class="btn-press text-[11px] bg-gray-800 hover:bg-gray-700 border border-gray-700 px-2.5 py-1 rounded-md text-gray-300 hover:text-white flex items-center gap-1.5 transition-all">
                    <span id="refresh-state-icon" class="inline-block">🔄</span>
                    <span>Refresh</span>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-3 flex-none">

                {{-- Cart panel --}}
                <div id="cart-panel" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between gap-2 px-4 py-2.5 border-b border-gray-800">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-base">🛒</span>
                            <span class="text-xs font-bold text-gray-300 uppercase tracking-wider">Current Cart</span>
                            <span id="cart-status-badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
                            <span id="cart-uuid" class="text-[10px] text-gray-600 font-mono truncate"></span>
                        </div>
                        <span id="cart-total" class="text-sm font-bold text-yellow-400 flex-none tabular-nums"></span>
                    </div>
                    <div id="cart-items" class="px-4 py-2.5 space-y-1 text-xs text-gray-300 overflow-y-auto max-h-40">
                        <div class="text-xs text-gray-500 italic" id="cart-empty">No active cart — click <span class="text-blue-400">Add</span> to start one.</div>
                    </div>
                </div>

                {{-- Order panel --}}
                <div id="order-panel" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between gap-2 px-4 py-2.5 border-b border-gray-800">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-base">📦</span>
                            <span class="text-xs font-bold text-gray-300 uppercase tracking-wider">Current Order</span>
                            <span id="order-status-badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
                            <span id="order-uuid" class="text-[10px] text-gray-600 font-mono truncate"></span>
                        </div>
                        <span id="order-total" class="text-sm font-bold text-yellow-400 flex-none tabular-nums"></span>
                    </div>
                    <div class="px-4 py-2.5">
                        <div id="order-lifecycle" class="hidden grid grid-cols-4 gap-1.5 mb-2">
                            <div data-step="placed"    class="text-center text-[10px] font-bold py-1.5 rounded border border-gray-700 bg-gray-800/50 text-gray-500">Placed</div>
                            <div data-step="paid"      class="text-center text-[10px] font-bold py-1.5 rounded border border-gray-700 bg-gray-800/50 text-gray-500">Paid</div>
                            <div data-step="shipped"   class="text-center text-[10px] font-bold py-1.5 rounded border border-gray-700 bg-gray-800/50 text-gray-500">Shipped</div>
                            <div data-step="delivered" class="text-center text-[10px] font-bold py-1.5 rounded border border-gray-700 bg-gray-800/50 text-gray-500">Delivered</div>
                        </div>
                        <div id="order-meta" class="hidden text-[11px] text-gray-400 space-y-0.5"></div>
                        <div id="order-empty" class="text-xs text-gray-500 italic">No order yet — <span class="text-green-400">Checkout</span> an active cart to create one.</div>
                    </div>
                </div>
            </div>

            {{-- Live Event Stream --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl flex flex-col flex-1 min-h-0 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-800 flex-none">
                    <h2 class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-2">
                        <span class="text-green-400">●</span> Live Event Stream
                    </h2>
                    <span class="text-[11px] text-gray-600" id="event-count-label">{{ $stats['totalEvents'] }} events</span>
                </div>
                <div id="event-stream" class="overflow-y-auto flex-1 divide-y divide-gray-800/50 p-2">
                    @foreach ($recentEvents as $event)
                        @php
                            $eventClass = class_basename($event->event_class);
                            $aggregateType = str_contains($event->event_class, 'Cart') ? 'cart' : 'order';
                            $colors = [
                                'cart' => ['bg' => 'bg-blue-900/30', 'border' => 'border-blue-700/50', 'badge' => 'bg-blue-700 text-blue-100', 'icon' => '🛒'],
                                'order' => ['bg' => 'bg-green-900/30', 'border' => 'border-green-700/50', 'badge' => 'bg-green-700 text-green-100', 'icon' => '📦'],
                            ];
                            $color = $colors[$aggregateType] ?? $colors['order'];
                            $specialColors = [
                                'OrderShipped'   => ['bg' => 'bg-purple-900/30', 'border' => 'border-purple-700/50', 'badge' => 'bg-purple-700 text-purple-100', 'icon' => '🚀'],
                                'OrderDelivered' => ['bg' => 'bg-teal-900/30', 'border' => 'border-teal-700/50', 'badge' => 'bg-teal-700 text-teal-100', 'icon' => '✅'],
                                'OrderCancelled' => ['bg' => 'bg-red-900/30', 'border' => 'border-red-700/50', 'badge' => 'bg-red-700 text-red-100', 'icon' => '❌'],
                                'OrderRefunded'  => ['bg' => 'bg-orange-900/30', 'border' => 'border-orange-700/50', 'badge' => 'bg-orange-700 text-orange-100', 'icon' => '💰'],
                                'OrderPaid'      => ['bg' => 'bg-blue-900/30', 'border' => 'border-blue-700/50', 'badge' => 'bg-blue-700 text-blue-100', 'icon' => '💳'],
                            ];
                            if (isset($specialColors[$eventClass])) {
                                $color = $specialColors[$eventClass];
                            }
                        @endphp
                        <div class="flex items-start gap-2 p-2 rounded-md {{ $color['bg'] }} border {{ $color['border'] }} mb-1">
                            <span class="text-base flex-shrink-0 mt-0.5">{{ $color['icon'] }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $color['badge'] }}">{{ $eventClass }}</span>
                                    <span class="text-[11px] text-gray-500">{{ $event->created_at ? \Carbon\Carbon::parse($event->created_at)->diffForHumans() : 'just now' }}</span>
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5 truncate">
                                    UUID: {{ substr($event->aggregate_uuid ?? '', 0, 8) }}...
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: Feedback + Recent Orders --}}
        <div id="right-panel" class="col-span-3 min-w-0 min-h-0 flex flex-col gap-3">

            {{-- Response / Feedback (always reserves space) --}}
            <div id="feedback-panel" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden flex flex-col flex-none" style="height: 38%;">
                <div class="px-4 py-2 border-b border-gray-800 flex items-center justify-between flex-none">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Response</span>
                    <button onclick="document.getElementById('feedback-content').textContent='// waiting for next action…';" class="text-gray-600 hover:text-gray-400 text-base leading-none" title="Clear">&times;</button>
                </div>
                <pre id="feedback-content" class="text-[11px] text-gray-500 p-3 overflow-auto flex-1 m-0">// trigger an action to see the API response here…</pre>
            </div>

            {{-- Recent Orders --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden flex flex-col flex-1 min-h-0">
                <div class="px-4 py-2.5 border-b border-gray-800 flex-none">
                    <h2 class="text-xs font-bold text-gray-300 uppercase tracking-wider">Recent Orders</h2>
                </div>
                <div class="divide-y divide-gray-800 overflow-y-auto flex-1" id="recent-orders">
                    @foreach ($orders as $order)
                        <div class="px-3 py-2 flex items-center justify-between">
                            <div class="min-w-0">
                                <div class="text-[11px] text-gray-300 font-mono truncate">{{ substr($order->uuid, 0, 8) }}...</div>
                                <div class="text-[11px] text-gray-500 truncate">{{ $order->user_id }}</div>
                            </div>
                            <div class="text-right flex-none ml-2">
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold
                                    @if($order->status->value === 'placed') bg-green-900 text-green-300
                                    @elseif($order->status->value === 'paid') bg-blue-900 text-blue-300
                                    @elseif($order->status->value === 'shipped') bg-purple-900 text-purple-300
                                    @elseif($order->status->value === 'delivered') bg-teal-900 text-teal-300
                                    @elseif($order->status->value === 'cancelled') bg-red-900 text-red-300
                                    @else bg-orange-900 text-orange-300
                                    @endif
                                ">{{ $order->status->label() }}</span>
                                <div class="text-[11px] text-gray-400 mt-0.5">${{ number_format($order->total_in_cents / 100, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // State
    const SESSION_KEY = 'eventSourcingDemo.session';
    function loadSession() {
        try {
            const raw = localStorage.getItem(SESSION_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (_) { return {}; }
    }
    function saveSession() {
        try {
            localStorage.setItem(SESSION_KEY, JSON.stringify({ cartUuid: currentCartUuid, orderUuid: currentOrderUuid }));
        } catch (_) {}
    }
    const _session = loadSession();
    let currentCartUuid = _session.cartUuid || null;
    let currentOrderUuid = _session.orderUuid || null;

    // Event type config
    const EVENT_CONFIG = {
        ItemAddedToCart:     { icon: '🛒', bg: 'bg-blue-900/30',   border: 'border-blue-700/50',   badge: 'bg-blue-700 text-blue-100' },
        ItemRemovedFromCart: { icon: '🛒', bg: 'bg-blue-900/30',   border: 'border-blue-700/50',   badge: 'bg-blue-700 text-blue-100' },
        CartCleared:         { icon: '🛒', bg: 'bg-blue-900/30',   border: 'border-blue-700/50',   badge: 'bg-blue-700 text-blue-100' },
        CheckoutStarted:     { icon: '🛒', bg: 'bg-blue-900/30',   border: 'border-blue-700/50',   badge: 'bg-blue-700 text-blue-100' },
        OrderPlaced:         { icon: '📦', bg: 'bg-green-900/30',  border: 'border-green-700/50',  badge: 'bg-green-700 text-green-100' },
        OrderPaid:           { icon: '💳', bg: 'bg-blue-900/30',   border: 'border-blue-700/50',   badge: 'bg-blue-700 text-blue-100' },
        OrderShipped:        { icon: '🚀', bg: 'bg-purple-900/30', border: 'border-purple-700/50', badge: 'bg-purple-700 text-purple-100' },
        OrderDelivered:      { icon: '✅', bg: 'bg-teal-900/30',   border: 'border-teal-700/50',   badge: 'bg-teal-700 text-teal-100' },
        OrderCancelled:      { icon: '❌', bg: 'bg-red-900/30',    border: 'border-red-700/50',    badge: 'bg-red-700 text-red-100' },
        OrderRefunded:       { icon: '💰', bg: 'bg-orange-900/30', border: 'border-orange-700/50', badge: 'bg-orange-700 text-orange-100' },
    };

    function relativeTime(iso) {
        if (!iso) return 'just now';
        const t = new Date(iso).getTime();
        if (isNaN(t)) return 'just now';
        const diff = Math.round((Date.now() - t) / 1000);
        if (diff < 5) return 'just now';
        if (diff < 60) return diff + 's ago';
        const m = Math.round(diff / 60);
        if (m < 60) return m + (m === 1 ? ' minute ago' : ' minutes ago');
        const h = Math.round(m / 60);
        if (h < 24) return h + (h === 1 ? ' hour ago' : ' hours ago');
        const d = Math.round(h / 24);
        return d + (d === 1 ? ' day ago' : ' days ago');
    }

    function createEventCard(data) {
        const cfg = EVENT_CONFIG[data.eventName] || EVENT_CONFIG['OrderPlaced'];
        const payloadStr = Object.entries(data.payload || {})
            .map(([k, v]) => `${k}: ${v}`)
            .join(' · ') || '';

        const card = document.createElement('div');
        card.className = `flex items-start gap-3 p-3 rounded-lg ${cfg.bg} border ${cfg.border} mb-1.5 event-slide-in event-highlight`;
        card.innerHTML = `
            <span class="text-lg flex-shrink-0 mt-0.5">${cfg.icon}</span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full ${cfg.badge}">${data.eventName}</span>
                    <span class="text-xs text-gray-500">${relativeTime(data.occurredAt)}</span>
                </div>
                ${payloadStr ? `<div class="text-xs text-gray-400 mt-1">${payloadStr}</div>` : ''}
                <div class="text-xs text-gray-600 mt-0.5 truncate">UUID: ${(data.aggregateUuid || '').substring(0, 8)}...</div>
            </div>
        `;
        return card;
    }

    function prependEvent(data) {
        const stream = document.getElementById('event-stream');
        const card = createEventCard(data);
        stream.prepend(card);

        // Remove highlight after animation
        setTimeout(() => card.classList.remove('event-highlight'), 2000);
    }

    function updateStats(stats) {
        document.getElementById('stat-total-events').textContent = stats.totalEvents ?? '';
        document.getElementById('stat-active-carts').textContent = stats.activeCarts ?? '';
        document.getElementById('stat-orders-today').textContent = stats.ordersToday ?? '';
        document.getElementById('stat-revenue-today').textContent = stats.revenueToday ?? '';
        const label = document.getElementById('event-count-label');
        if (label) label.textContent = (stats.totalEvents ?? '') + ' events';
    }

    function showFeedback(data, isError = false) {
        const panel = document.getElementById('feedback-panel');
        const content = document.getElementById('feedback-content');
        if (panel) panel.classList.remove('hidden');
        content.className = `text-[11px] p-3 overflow-auto flex-1 m-0 ${isError ? 'text-red-300' : 'text-green-300'}`;
        content.textContent = JSON.stringify(data, null, 2);
    }

    async function triggerAction(action, extraBody = {}) {
        if (action === 'start-new') {
            currentCartUuid = null;
            currentOrderUuid = null;
            saveSession();
            currentState = { cart: null, order: null };
            renderCartPanel(null);
            renderOrderPanel(null);
            refreshButtonStates();
            showFeedback({ message: 'Session reset — Add to Cart will start a fresh customer.' });
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const body = { ...extraBody };

            if (action === 'add-to-cart') {
                const sel = document.getElementById('product-select');
                if (sel && sel.value) body.product_id = sel.value;
                if (currentCartUuid) body.cart_uuid = currentCartUuid;
            }
            if (['remove-from-cart', 'checkout'].includes(action) && currentCartUuid) {
                body.cart_uuid = currentCartUuid;
            }
            if (action === 'remove-from-cart') {
                const sel = document.getElementById('product-select');
                if (sel && sel.value) body.product_id = sel.value;
            }
            if (['mark-as-paid', 'mark-as-shipped', 'mark-as-delivered', 'cancel-order', 'refund-order'].includes(action) && currentOrderUuid) {
                body.order_uuid = currentOrderUuid;
            }

            if (action === 'replay-events') {
                const stream = document.getElementById('event-stream');
                if (stream) stream.innerHTML = '';
            }

            const response = await fetch(`/demo/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            });

            const data = await response.json();

            if (!response.ok) {
                showFeedback(data, true);
                return;
            }

            showFeedback(data);

            // Track state
            if (data.cart_uuid) currentCartUuid = data.cart_uuid;
            if (data.order_uuid) currentOrderUuid = data.order_uuid;
            saveSession();

            // Refresh stats
            const statsRes = await fetch('/api/stats');
            const stats = await statsRes.json();
            updateStats(stats);

            // Refresh state-driven panels + button enablement
            await refreshState();

        } catch (err) {
            showFeedback({ error: String(err) }, true);
        }
    }

    // ---- State-driven panels + button enablement ----
    let currentState = { cart: null, order: null };

    const CART_STATUS_BADGE = {
        active:        'bg-blue-900 text-blue-200',
        checking_out:  'bg-yellow-900 text-yellow-200',
        closed:        'bg-gray-700 text-gray-200',
    };
    const ORDER_STATUS_BADGE = {
        placed:    'bg-green-900 text-green-200',
        paid:      'bg-blue-900 text-blue-200',
        shipped:   'bg-purple-900 text-purple-200',
        delivered: 'bg-teal-900 text-teal-200',
        cancelled: 'bg-red-900 text-red-200',
        refunded:  'bg-orange-900 text-orange-200',
    };
    const LIFECYCLE_ORDER = ['placed', 'paid', 'shipped', 'delivered'];
    const LIFECYCLE_ACTIVE_CLASSES = {
        placed:    'bg-green-700 border-green-500 text-green-50',
        paid:      'bg-blue-700 border-blue-500 text-blue-50',
        shipped:   'bg-purple-700 border-purple-500 text-purple-50',
        delivered: 'bg-teal-700 border-teal-500 text-teal-50',
    };

    function renderCartPanel(cart) {
        const badge   = document.getElementById('cart-status-badge');
        const uuidEl  = document.getElementById('cart-uuid');
        const totalEl = document.getElementById('cart-total');
        const itemsEl = document.getElementById('cart-items');

        if (!cart) {
            badge.classList.add('hidden');
            uuidEl.textContent = '';
            totalEl.textContent = '';
            itemsEl.innerHTML = '<div class="text-[10px] text-gray-600 italic">No active cart — click Add to Cart to start one.</div>';
            return;
        }

        badge.className = `text-[9px] font-bold px-1.5 py-0.5 rounded-full ${CART_STATUS_BADGE[cart.status] || 'bg-gray-700 text-gray-200'}`;
        badge.textContent = cart.status;
        badge.classList.remove('hidden');
        uuidEl.textContent = (cart.uuid || '').substring(0, 8);
        totalEl.textContent = cart.totalFormatted;

        if (!cart.items || cart.items.length === 0) {
            itemsEl.innerHTML = '<div class="text-[10px] text-gray-600 italic">Cart is empty.</div>';
            return;
        }

        itemsEl.innerHTML = cart.items.map(it => `
            <div class="flex items-center justify-between gap-2">
                <span class="truncate text-gray-300">${escapeHtml(it.productName)}</span>
                <span class="flex-none text-gray-500">×${it.quantity}</span>
                <span class="flex-none text-gray-400 tabular-nums">$${(it.lineTotal/100).toFixed(2)}</span>
            </div>
        `).join('');
    }

    function renderOrderPanel(order) {
        const badge      = document.getElementById('order-status-badge');
        const uuidEl     = document.getElementById('order-uuid');
        const totalEl    = document.getElementById('order-total');
        const lifecycle  = document.getElementById('order-lifecycle');
        const metaEl     = document.getElementById('order-meta');
        const emptyEl    = document.getElementById('order-empty');

        if (!order) {
            badge.classList.add('hidden');
            uuidEl.textContent = '';
            totalEl.textContent = '';
            lifecycle.classList.add('hidden');
            metaEl.classList.add('hidden');
            metaEl.innerHTML = '';
            emptyEl.classList.remove('hidden');
            return;
        }

        emptyEl.classList.add('hidden');
        badge.className = `text-[9px] font-bold px-1.5 py-0.5 rounded-full ${ORDER_STATUS_BADGE[order.status] || 'bg-gray-700 text-gray-200'}`;
        badge.textContent = order.status;
        badge.classList.remove('hidden');
        uuidEl.textContent = (order.uuid || '').substring(0, 8);
        totalEl.textContent = order.totalFormatted;

        // Lifecycle pills
        lifecycle.classList.remove('hidden');
        const reachedIdx = LIFECYCLE_ORDER.indexOf(order.status);
        lifecycle.querySelectorAll('[data-step]').forEach(el => {
            const step = el.getAttribute('data-step');
            const idx  = LIFECYCLE_ORDER.indexOf(step);
            el.className = 'text-center text-[9px] font-bold py-1 rounded border';
            if (reachedIdx >= 0 && idx <= reachedIdx) {
                el.className += ' ' + LIFECYCLE_ACTIVE_CLASSES[step];
            } else {
                el.className += ' border-gray-700 bg-gray-800/50 text-gray-600';
            }
        });

        // Meta (cancellation/refund + payment + tracking)
        const metaParts = [];
        if (order.status === 'cancelled') {
            metaParts.push(`<div><span class="px-1.5 py-0.5 rounded-full bg-red-900 text-red-200 text-[9px] font-bold">CANCELLED</span> ${order.cancellationReason ? escapeHtml(order.cancellationReason) : ''}</div>`);
        }
        if (order.status === 'refunded') {
            metaParts.push(`<div><span class="px-1.5 py-0.5 rounded-full bg-orange-900 text-orange-200 text-[9px] font-bold">REFUNDED</span></div>`);
        }
        if (order.paymentReference) {
            metaParts.push(`<div>pay: <span class="text-gray-400 font-mono">${escapeHtml(order.paymentReference)}</span></div>`);
        }
        if (order.trackingNumber) {
            metaParts.push(`<div>track: <span class="text-gray-400 font-mono">${escapeHtml(order.trackingNumber)}</span></div>`);
        }
        if (metaParts.length) {
            metaEl.innerHTML = metaParts.join('');
            metaEl.classList.remove('hidden');
        } else {
            metaEl.innerHTML = '';
            metaEl.classList.add('hidden');
        }
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
    }

    function actionState(action, state) {
        const cart  = state.cart;
        const order = state.order;
        const cartActive = cart && cart.status === 'active';
        const cartHasItems = cartActive && cart.itemCount > 0;
        switch (action) {
            case 'add-to-cart':
                if (cart && !cartActive) return { enabled: false, reason: `Current cart is ${cart.status} — start a new cart by clearing the selection.` };
                return { enabled: true, reason: 'Add a product to the active cart (creates one if none exists).' };
            case 'remove-from-cart':
                if (!cart) return { enabled: false, reason: 'No cart yet.' };
                if (!cartActive) return { enabled: false, reason: `Cart is ${cart.status}; cannot modify after checkout.` };
                if (!cartHasItems) return { enabled: false, reason: 'Cart has no items to remove.' };
                return { enabled: true, reason: 'Remove one unit of an item from the cart.' };
            case 'checkout':
                if (!cart) return { enabled: false, reason: 'No cart yet.' };
                if (!cartActive) return { enabled: false, reason: `Cart is ${cart.status}; already checked out.` };
                if (!cartHasItems) return { enabled: false, reason: 'Cart is empty — add items first.' };
                return { enabled: true, reason: 'Place an order from the active cart.' };
            case 'mark-as-paid':
                if (!order) return { enabled: false, reason: 'No order yet — checkout first.' };
                if (order.status !== 'placed') return { enabled: false, reason: `Order is ${order.status}; can only pay when status is placed.` };
                return { enabled: true, reason: 'Mark the order as paid.' };
            case 'mark-as-shipped':
                if (!order) return { enabled: false, reason: 'No order yet.' };
                if (order.status !== 'paid') return { enabled: false, reason: `Order is ${order.status}; must be paid before shipping.` };
                return { enabled: true, reason: 'Mark the order as shipped.' };
            case 'mark-as-delivered':
                if (!order) return { enabled: false, reason: 'No order yet.' };
                if (order.status !== 'shipped') return { enabled: false, reason: `Order is ${order.status}; must be shipped before delivery.` };
                return { enabled: true, reason: 'Mark the order as delivered.' };
            case 'cancel-order':
                if (!order) return { enabled: false, reason: 'No order yet.' };
                if (!['placed','paid'].includes(order.status)) return { enabled: false, reason: `Order is ${order.status}; can only cancel before shipping.` };
                return { enabled: true, reason: 'Cancel the order.' };
            case 'refund-order':
                if (!order) return { enabled: false, reason: 'No order yet.' };
                if (!['paid','shipped','delivered'].includes(order.status)) return { enabled: false, reason: `Order is ${order.status}; can only refund a paid order.` };
                return { enabled: true, reason: 'Refund the order.' };
            case 'replay-events':
                return { enabled: true, reason: 'Re-project all events into the read models.' };
            case 'start-new': {
                const terminal = order && ['delivered', 'cancelled', 'refunded'].includes(order.status);
                if (terminal) return { enabled: true, reason: `Order is ${order.status}; reset session for a new customer.` };
                if (!cart && !order) return { enabled: true, reason: 'No active cart yet — click to start fresh.' };
                return { enabled: false, reason: 'Complete or cancel the current order before starting a new session.' };
            }
            default:
                return { enabled: true, reason: '' };
        }
    }

    const DISABLED_CLASSES = ['opacity-40', 'cursor-not-allowed', 'grayscale'];

    function refreshButtonStates() {
        document.querySelectorAll('[data-action]').forEach(btn => {
            const action = btn.getAttribute('data-action');
            const { enabled, reason } = actionState(action, currentState);
            btn.title = reason;
            if (enabled) {
                btn.disabled = false;
                btn.classList.remove(...DISABLED_CLASSES);
                btn.removeAttribute('aria-disabled');
            } else {
                btn.disabled = true;
                btn.classList.add(...DISABLED_CLASSES);
                btn.setAttribute('aria-disabled', 'true');
            }
        });
    }

    let refreshStateGen = 0;
    async function refreshState() {
        const gen = ++refreshStateGen;
        const params = new URLSearchParams();
        if (currentCartUuid) params.set('cart_uuid', currentCartUuid);
        if (currentOrderUuid) params.set('order_uuid', currentOrderUuid);
        const qs = params.toString();
        try {
            const res = await fetch('/api/state' + (qs ? ('?' + qs) : ''));
            const data = await res.json();
            if (gen !== refreshStateGen) return; // stale response — newer refresh in flight
            currentState = data || { cart: null, order: null };
            if (currentState.cart && currentState.cart.uuid) currentCartUuid = currentState.cart.uuid;
            if (currentState.order && currentState.order.uuid) currentOrderUuid = currentState.order.uuid;
            saveSession();
            renderCartPanel(currentState.cart);
            renderOrderPanel(currentState.order);
            refreshButtonStates();
        } catch (e) {
            // leave previous state
        }
    }

    // Wire up buttons
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.disabled) return;
            const action = btn.getAttribute('data-action');
            triggerAction(action);
        });
    });

    // Architecture overlay
    function toggleArchitecture() {
        const overlay = document.getElementById('arch-overlay');
        overlay.classList.toggle('hidden');
    }

    // Resizable split removed in redesign — fixed 3-column grid keeps everything visible.

    // Initial state load + initial button enablement (before user interacts)
    refreshButtonStates();
    refreshState();

    // Manual refresh button
    const refreshBtn = document.getElementById('refresh-state-btn');
    const refreshIcon = document.getElementById('refresh-state-icon');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
            refreshBtn.disabled = true;
            refreshIcon.classList.add('animate-spin');
            try {
                await refreshState();
                const statsRes = await fetch('/api/stats');
                if (statsRes.ok) updateStats(await statsRes.json());
            } finally {
                setTimeout(() => {
                    refreshIcon.classList.remove('animate-spin');
                    refreshBtn.disabled = false;
                }, 300);
            }
        });
    }

    // WebSocket / Socket Conveyor — init after all modules (including Vite bundle) load.
    window._wsReady = false;
    window.addEventListener('load', () => {
        const cfgEl = document.getElementById('conveyor-config');
        const cfg = cfgEl ? JSON.parse(cfgEl.textContent) : null;
        if (cfg && typeof window.Conveyor !== 'undefined') {
            new window.Conveyor({
                protocol: cfg.protocol,
                uri: window.location.hostname || cfg.uri,
                port: cfg.port,
                channel: cfg.channel,
                onReady: () => { window._wsReady = true; },
                onMessage: (data) => {
                    if (typeof data === 'object' && data !== null && data.eventName) {
                        prependEvent(data);
                        refreshState();
                    }
                },
            });
        }
    });
</script>
</body>
</html>
