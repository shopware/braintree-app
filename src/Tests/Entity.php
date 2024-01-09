<?php declare(strict_types=1);

namespace Swag\Braintree\Tests;

use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Entity\Contract\EntityTrait;

#[IgnoreClassForCodeCoverage(Entity::class)]
class Entity implements EntityInterface
{
    use EntityTrait;
}
