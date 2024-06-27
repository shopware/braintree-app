<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\ArgumentResolver\Dto;

use Symfony\Component\Uid\Uuid;

class Criteria
{
    /**
     * @var Uuid[]
     */
    public array $ids = [];
}
