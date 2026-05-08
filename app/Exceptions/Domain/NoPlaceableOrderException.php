<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class NoPlaceableOrderException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No placed orders found');
    }
}
