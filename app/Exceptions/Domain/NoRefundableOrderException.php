<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class NoRefundableOrderException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No delivered orders found for refund');
    }
}
