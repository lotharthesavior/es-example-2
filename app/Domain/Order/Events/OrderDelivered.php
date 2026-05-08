<?php

declare(strict_types=1);

namespace App\Domain\Order\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class OrderDelivered extends ShouldBeStored {}
