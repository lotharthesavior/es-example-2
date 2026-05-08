<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class NoDeliverableOrderException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No shipped orders found');
    }
}
