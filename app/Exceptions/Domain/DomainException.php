<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

abstract class DomainException extends \RuntimeException
{
    public int $httpStatus = 422;
}
