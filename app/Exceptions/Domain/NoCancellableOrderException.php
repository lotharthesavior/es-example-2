<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class NoCancellableOrderException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No cancellable orders found');
    }
}
