<?php declare(strict_types=1);

namespace Swag\Braintree\Entity\Contract;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
trait EntityTrait
{
    use EntityDateTrait;
    use EntityIdTrait;
}
