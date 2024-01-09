<?php declare(strict_types=1);

namespace Swag\Braintree\Tests;

use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;
use Swag\Braintree\Repository\AbstractRepository;

/**
 * @extends AbstractRepository<Entity>
 */
#[IgnoreClassForCodeCoverage(Repository::class)]
class Repository extends AbstractRepository
{
}
