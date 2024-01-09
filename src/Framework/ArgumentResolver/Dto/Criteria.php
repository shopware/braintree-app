<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\ArgumentResolver\Dto;

use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;
use Symfony\Component\Uid\Uuid;

#[IgnoreClassForCodeCoverage(Criteria::class)]
class Criteria
{
    /**
     * @var Uuid[]
     */
    public array $ids = [];
}
