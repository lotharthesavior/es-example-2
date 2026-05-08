<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class NoShippableOrderException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No paid orders found');
    }
}
