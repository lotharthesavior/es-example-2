<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Building Resilient PHP Applications with an Event-Driven Mindset — PHPTek 2026</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/dist/reset.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/dist/reveal.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/dist/theme/black.css" id="theme">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/plugin/highlight/monokai.css">

    <style>
        :root {
            --accent: #00d4ff;
            --accent-2: #ff6b9d;
        }

        .reveal h1,
        .reveal h2,
        .reveal h3 {
            text-transform: none;
            letter-spacing: -0.02em;
        }

        .reveal h1 {
            color: var(--accent);
        }

        .reveal h2 {
            color: var(--accent);
        }

        .reveal .accent {
            color: var(--accent);
        }

        .reveal .accent-2 {
            color: var(--accent-2);
        }

        .reveal .muted {
            color: #888;
            font-size: 0.7em;
        }

        .reveal .big-number {
            font-size: 4em;
            font-weight: 800;
            color: var(--accent-2);
            line-height: 1;
        }

        .reveal .flex {
            display: flex;
            gap: 2rem;
            align-items: center;
            justify-content: center;
        }

        .reveal .card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            display: inline-block;
            margin: 0.5rem;
        }

        .reveal pre {
            box-shadow: none;
            font-size: 0.5em;
        }

        .reveal pre code {
            padding: 1rem;
            border-radius: 8px;
        }

        .reveal ul {
            list-style: none;
            padding: 0;
        }

        .reveal ul li {
            margin: 0.5em 0;
            padding-left: 1.2em;
            position: relative;
        }

        .reveal ul li::before {
            content: "▸";
            color: var(--accent);
            position: absolute;
            left: 0;
        }

        .reveal .arrow {
            font-size: 2em;
            color: var(--accent);
        }

        .reveal .node {
            background: #1e2a3a;
            border: 2px solid var(--accent);
            padding: 0.8em 1.5em;
            border-radius: 8px;
            display: inline-block;
            margin: 0 0.5em;
            font-weight: 600;
        }

        .reveal .node.alt {
            background: #2a1e3a;
            border-color: var(--accent-2);
        }

        .reveal .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            align-items: start;
            text-align: left;
        }

        .reveal .col-label {
            font-size: 0.7em;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--accent);
            margin-bottom: 0.5em;
        }

        .reveal .col-label.alt {
            color: var(--accent-2);
        }

        .reveal footer {
            position: fixed;
            bottom: 12px;
            left: 20px;
            font-size: 14px;
            color: #555;
        }

        .reveal .slide-number {
            color: #666;
        }

        .reveal blockquote {
            border-left: 4px solid var(--accent);
            background: #151515;
            padding: 1em 1.5em;
            font-style: normal;
        }

        .reveal .timeline {
            display: flex;
            gap: 0.5em;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1.5em;
        }

        .reveal .event-pill {
            background: #1e2a3a;
            border: 1px solid var(--accent);
            border-radius: 999px;
            padding: 0.4em 1em;
            font-size: 0.7em;
        }
    </style>
</head>

<body>
    <div class="reveal">
        <div class="slides">

            <!-- Slide 1: Title -->
            <section>
                <h1>Building Resilient PHP Apps</h1>
                <h2 style="margin-top:-0.3em;">with an <span class="accent-2">Event-Driven Mindset</span></h2>
                <p style="margin-top:2em;">by <strong>Savio Resende</strong></p>
                <p class="muted">PHPTek 2026</p>
            </section>

            <!-- Disabled Slide: Speaker -->
            <!--<section>
        <h2>Savio Resende</h2>
        <p><a href="https://savioresende.com">savioresende.com</a></p>
        <p class="muted">@lotharthesavior</p>
      </section>-->

            <!-- Slide 2: A Friday Afternoon -->
            <section>
                <h2>A Friday Afternoon…</h2>
                <blockquote style="margin-top: 1.5em; font-size: 0.85em;">
                    Your team ships an hotfix. By Monday, <span class="accent-2">10,000 order statuses</span>
                    silently flipped from <em>processing</em> to <em>shipped</em> — but nothing actually shipped.
                </blockquote>
                <p class="muted" style="margin-top: 2em;">What was the correct state? You don't know. The history is
                    gone.</p>
            </section>

            <!-- Slide 3: The Real Problem -->
            <section>
                <h2>The Real Problem</h2>
                <ul style="font-size: 0.9em;">
                    <li>Traditional systems <strong class="accent-2">overwrite state</strong> on every update</li>
                    <li>You know <strong>what is</strong>. Not <strong>what was</strong>. Never <strong>why</strong>.
                    </li>
                    <li>History tables capture snapshots — not <span class="accent">intent</span></li>
                </ul>
            </section>

            <!-- Slide 4: Talk Objective -->
            <section>
                <h2>Talk Objective</h2>
                <ol style="font-size: 0.9em;">
                    <li><strong class="accent">What</strong> is event sourcing — and why now?</li>
                    <li><strong class="accent">How</strong> to apply it in PHP <span class="muted">(Laravel +
                            Spatie)</span></li>
                    <li><strong class="accent">Where</strong> it pays off — and where it doesn't</li>
                </ol>
            </section>

            <!-- Slide 5: Traditional vs Event-Sourced -->
            <section>
                <h2>Same Feature, Two Mindsets</h2>
                <div class="two-col" style="margin-top: 1em;">
                    <div>
                        <div class="col-label alt">Traditional</div>
                        <ul style="font-size: 0.75em;">
                            <li>INSERT order row</li>
                            <li>UPDATE status = 'paid'</li>
                            <li>UPDATE status = 'shipped'</li>
                            <li>Logs? Maybe.</li>
                        </ul>
                    </div>
                    <div>
                        <div class="col-label">Event-Sourced</div>
                        <ul style="font-size: 0.75em;">
                            <li>OrderPlaced</li>
                            <li>PaymentReceived</li>
                            <li>OrderShipped</li>
                            <li>Every fact, immutable</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Slide 6: Mindset shift -->
            <section>
                <h2>The Mindset Shift</h2>
                <blockquote style="font-size: 0.9em;">
                    Stop modeling <span class="accent-2">state</span>.<br>
                    Start modeling <span class="accent">behavior</span>.
                </blockquote>
                <p class="muted" style="margin-top: 1.5em;">State becomes a consequence — not the source of truth.</p>
            </section>

            <!-- Slide 7: Core Concepts overview -->
            <section>
                <h2>Core Concepts</h2>
                <div style="margin-top: 1.5em;">
                    <span class="card"><strong class="accent">Events</strong></span>
                    <span class="card"><strong class="accent">Event Store</strong></span>
                    <span class="card"><strong class="accent">Aggregates</strong></span>
                    <span class="card"><strong class="accent">Projections</strong></span>
                    <span class="card"><strong class="accent">Reactors</strong></span>
                </div>
            </section>

            <!-- Slide 8: Events -->
            <section>
                <h3>Events</h3>
                <ul style="font-size: 0.85em;">
                    <li>Immutable facts about something that <strong>happened</strong></li>
                    <li>Always <span class="accent">past tense</span> — completed actions</li>
                    <li>Never deleted, never modified</li>
                    <li>Mistakes? Record a <span class="accent-2">compensating event</span> (Cancelled, Refunded)</li>
                </ul>
                <div class="timeline">
                    <span class="event-pill">OrderPlaced</span>
                    <span class="event-pill">PaymentReceived</span>
                    <span class="event-pill">OrderShipped</span>
                </div>
            </section>

            <!-- Slide 9: Event Store -->
            <section>
                <h3>The Event Store</h3>
                <ul style="font-size: 0.9em;">
                    <li>Append-only log</li>
                    <li>Add to the end — never modify, never remove</li>
                    <li>Your system's <span class="accent">single source of truth</span></li>
                </ul>
                <p class="muted" style="margin-top: 1.5em;">Think: git, but for your domain.</p>
            </section>

            <!-- Slide 10: Aggregates -->
            <section>
                <h3>Aggregates</h3>
                <ul style="font-size: 0.85em;">
                    <li>Where business rules live</li>
                    <li>Cluster of related data that must stay consistent</li>
                    <li>Loads history → applies rules → emits new events</li>
                </ul>
                <blockquote style="margin-top: 1em; font-size: 0.8em;">
                    Can't ship an unpaid order. Can't cancel a delivered one.
                </blockquote>
            </section>

            <!-- Slide 11: Projections -->
            <section>
                <h3>Projections</h3>
                <ul style="font-size: 0.85em;">
                    <li>Read-optimized views, built from events</li>
                    <li>One projection per question your app needs to answer</li>
                    <li>New question? Build a new projection — replay all events</li>
                </ul>
                <p class="muted" style="margin-top: 1.5em;">You can answer questions you didn't know to ask.</p>
            </section>

            <!-- Slide 12: Reactors -->
            <section>
                <h3>Reactors</h3>
                <ul style="font-size: 0.85em;">
                    <li>Side effects triggered by events</li>
                    <li>Aggregates stay clean — they don't know about emails or alerts</li>
                    <li>Reactors respond to facts</li>
                </ul>
                <p class="muted" style="margin-top: 1.5em;">DangerousHeartRate → page the on-call physician.</p>
            </section>

            <!-- Slide 13: The Mental Model -->
            <section>
                <h2>The Mental Model</h2>
                <blockquote style="font-size: 0.9em;">
                    state = fold(events, initialState)
                </blockquote>
                <p class="muted" style="margin-top: 1.5em;">
                    Like a bank balance — just the sum of every deposit minus every withdrawal.
                </p>
            </section>

            <!-- Slide 14: Flow diagram -->
            <section>
                <h2>The Flow</h2>
                <div class="flex" style="margin-top: 2em; flex-wrap: wrap;">
                    <span class="node">Command</span>
                    <span class="arrow">→</span>
                    <span class="node">Aggregate</span>
                    <span class="arrow">→</span>
                    <span class="node alt">Event</span>
                    <span class="arrow">→</span>
                    <span class="node">Projector</span>
                </div>
                <div class="flex" style="margin-top: 1em;">
                    <span class="muted">└─ Event also fans out to →</span>
                    <span class="node alt">Reactor</span>
                </div>
            </section>

            <!-- Slide 15: Examining the Code intro -->
            <section>
                <h2>Examining the Code</h2>
                <p style="font-size: 1.2em;">1. E-commerce Dashboard</p>
                <p style="font-size: 1.2em;">2. Health Metrics Tracker</p>
                <p class="muted" style="margin-top: 1.5em;">Heart rate, blood pressure, oxygen — events all the way
                    down.</p>
            </section>

            <!-- Slide 16: Code - The event -->
            <section>
                <h3>The Event</h3>
                <pre><code class="language-php" data-trim data-noescape>use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class HeartRateRecorded extends ShouldBeStored
{
    public function __construct(
        public string $patientId,
        public int $bpm,
        public string $recordedAt,
    ) {}
}</code></pre>
            </section>

            <!-- Slide 17: Code - The aggregate -->
            <section>
                <h3>The Aggregate</h3>
                <pre><code class="language-php" data-trim data-noescape>use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class PatientAggregate extends AggregateRoot
{
    public function recordHeartRate(int $bpm): self
    {
        $this->recordThat(new HeartRateRecorded(
            patientId: $this->uuid(),
            bpm: $bpm,
            recordedAt: now()->toIso8601String(),
        ));

        return $this;
    }
}</code></pre>
            </section>

            <!-- Slide 18: Code - The projector -->
            <section>
                <h3>The Projector</h3>
                <pre><code class="language-php" data-trim data-noescape>use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class HealthDashboardProjector extends Projector
{
    public function onHeartRateRecorded(HeartRateRecorded $event): void
    {
        HealthReading::writeable()->create([
            'patient_id' => $event->patientId,
            'metric'     => 'heart_rate',
            'value'      => $event->bpm,
            'recorded_at'=> $event->recordedAt,
        ]);
    }
}</code></pre>
            </section>

            <!-- Slide 19: Code - The reactor -->
            <section>
                <h3>The Reactor</h3>
                <pre><code class="language-php" data-trim data-noescape>use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class DangerousHeartRateReactor extends Reactor
{
    public function onHeartRateRecorded(HeartRateRecorded $event): void
    {
        if ($event->bpm > 150) {
            Alert::page(
                team: 'medical',
                patient: $event->patientId,
                bpm: $event->bpm,
            );
        }
    }
}</code></pre>
            </section>

            <!-- Slide 20: Time machine -->
            <section>
                <h2>The Time Machine</h2>
                <pre><code class="language-php" data-trim data-noescape>// Replay every event up to a moment in history
PatientAggregate::retrieve($patientId)
    ->replay(until: '2026-01-15 09:00');</code></pre>
                <p class="muted" style="margin-top: 1.5em;">Reconstruct exact state at any point in the past.</p>
            </section>

            <!-- Slide 21: Hard parts intro -->
            <section>
                <h2>The Hard Parts</h2>
                <p class="muted">…because nothing this good is free.</p>
            </section>

            <!-- Slide 22: Idempotency -->
            <section>
                <h3>Idempotency</h3>
                <ul style="font-size: 0.85em;">
                    <li>Same event delivered twice → projection double-counts</li>
                    <li>Track which event IDs you've already processed</li>
                    <li>Check before applying</li>
                </ul>
                <p class="muted" style="margin-top: 1.5em;">A duplicate <code>PaymentReceived</code> is not a free
                    coffee.</p>
            </section>

            <!-- Slide 23: Versioning -->
            <section>
                <h3>Event Versioning</h3>
                <ul style="font-size: 0.85em;">
                    <li>Six months in: <code>OrderPlaced</code> needs a new field</li>
                    <li>Millions of old events don't have it</li>
                    <li>Solution: <span class="accent">upcasting</span> — transform on load</li>
                    <li>Or version events explicitly and handle each shape</li>
                </ul>
            </section>

            <!-- Slide 24: Rebuild performance -->
            <section>
                <h3>Projection Rebuilds</h3>
                <p class="big-number">50M</p>
                <p class="muted">events. Now what?</p>
                <ul style="font-size: 0.8em; margin-top: 1.5em;">
                    <li>Snapshots — checkpoints to skip ahead</li>
                </ul>
            </section>

            <!-- Slide 25: Testing -->
            <section>
                <h3>Testing</h3>
                <pre><code class="language-php" data-trim data-noescape>// Given / When / Then
PatientAggregate::fake($patientId)
    ->given([
        new HeartRateRecorded($patientId, 80,  '...'),
        new HeartRateRecorded($patientId, 95,  '...'),
    ])
    ->when(fn ($p) => $p->recordHeartRate(160))
    ->assertRecorded([
        new HeartRateRecorded($patientId, 160, '...'),
    ]);</code></pre>
                <p class="muted" style="margin-top: 0.5em;">Declarative. Explicit. Surprisingly readable.</p>
            </section>

            <!-- Slide 26: Migration / Strangler Fig -->
            <section>
                <h2>Adopting Incrementally</h2>
                <ul style="font-size: 0.85em;">
                    <li><strong class="accent">Strangler fig</strong> — grow around the old system</li>
                    <li>Pick the module with the most audit pain</li>
                </ul>
            </section>

            <!-- Slide 27: When NOT to -->
            <section>
                <h2>When NOT to Use It</h2>
                <ul style="font-size: 0.9em;">
                    <li>A simple blog</li>
                    <li>A CRUD admin panel</li>
                    <li>Anywhere "what happened" doesn't matter</li>
                </ul>
                <p class="muted" style="margin-top: 1.5em;">Event sourcing is a tool, not a religion.</p>
            </section>

            <!-- Slide 28: Where it pays off -->
            <section>
                <h2>Where It Earns Its Keep</h2>
                <div style="margin-top: 1.5em;">
                    <span class="card">Financial systems</span>
                    <span class="card">Healthcare</span>
                    <span class="card">E-commerce</span>
                    <span class="card">Compliance-heavy</span>
                </div>
                <p class="muted" style="margin-top: 1.5em;">Anywhere history is a feature, not an afterthought.</p>
            </section>

            <!-- Slide 29: Closing thought -->
            <section>
                <h2>The Real Shift</h2>
                <blockquote style="font-size: 0.9em;">
                    What <span class="accent">happened</span> matters more than what <span class="accent-2">is</span>.
                </blockquote>
                <p class="muted" style="margin-top: 1.5em;">
                    Capture facts. Derive state. Build systems that remember.
                </p>
            </section>

            <!-- Slide 30: Resources -->
            <section>
                <h2>Resources</h2>
                <ul style="font-size: 0.75em;">
                    <li><a
                            href="https://github.com/lotharthesavior/es-example-2">github.com/lotharthesavior/es-example-2</a>
                    </li>
                    <li><a
                            href="https://spatie.be/docs/laravel-event-sourcing">spatie.be/docs/laravel-event-sourcing</a>
                    </li>
                    <li>Greg Young — <em>CQRS &amp; Event Sourcing</em> (talks)</li>
                    <li>Martin Fowler — <em>Event Sourcing</em> pattern</li>
                </ul>
            </section>

            <!-- Slide 31: Thank You -->
            <section>
                <h1>Thank You</h1>
                <p style="margin-top: 2em;"><a href="https://savioresende.com">savioresende.com</a></p>
                <p class="muted">@lotharthesavior</p>
                <p><img src="even-sourcing-qr-code-joind.png" alt="">
                <p>
                <p style="font-size: 0.6em;">
                    https://joind.in/event/php-tek-2026/building-resilient-php-applications-with-an-event-driven-mindset
                </p>
            </section>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/dist/reveal.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/plugin/highlight/highlight.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/plugin/notes/notes.js"></script>
    <script>
        Reveal.initialize({
            hash: true,
            slideNumber: 'c/t',
            transition: 'slide',
            controls: true,
            progress: true,
            center: true,
            plugins: [RevealHighlight, RevealNotes]
        });
    </script>
</body>

</html>
