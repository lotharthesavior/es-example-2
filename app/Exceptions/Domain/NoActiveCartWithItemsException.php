<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class NoActiveCartWithItemsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No active carts with items');
    }
}
