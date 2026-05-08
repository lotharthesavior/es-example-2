<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class NoActiveCartToCheckoutException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No active carts to checkout');
    }
}
