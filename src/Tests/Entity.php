<?php declare(strict_types=1);

namespace Swag\Braintree\Tests;

use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Entity\Contract\EntityTrait;

class Entity implements EntityInterface
{
    use EntityTrait;
}
