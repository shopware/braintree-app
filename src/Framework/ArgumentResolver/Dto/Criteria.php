<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\ArgumentResolver\Dto;

use Symfony\Component\Uid\Uuid;

/**
 * @codeCoverageIgnore
 */
class Criteria
{
    /**
     * @var Uuid[]
     */
    public array $ids = [];
}
