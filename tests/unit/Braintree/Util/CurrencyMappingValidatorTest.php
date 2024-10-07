<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Util;

use Braintree\MerchantAccount;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Util\CurrencyMappingValidator;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\CurrencyMappingRepository;

#[CoversClass(CurrencyMappingValidator::class)]
class CurrencyMappingValidatorTest extends TestCase
{
    private MockObject&CurrencyMappingRepository $currencyMappingRepository;

    private MockObject&BraintreeConnectionService $connectionService;

    private CurrencyMappingValidator $validator;

    protected function setUp(): void
    {
        $this->currencyMappingRepository = $this->createMock(CurrencyMappingRepository::class);
        $this->connectionService = $this->createMock(BraintreeConnectionService::class);

        $this->validator = new CurrencyMappingValidator(
            $this->currencyMappingRepository,
            $this->connectionService,
        );
    }

    public function testDeleteInvalidCurrencyMappings(): void
    {
        $accounts = [
            MerchantAccount::factory(['id' => 'merchant-id-1']),
            MerchantAccount::factory(['id' => 'merchant-id-2']),
        ];
        $shop = new ShopEntity('shop-id', '', '');

        $expressionBuilder = $this->createMock(\Doctrine\ORM\Query\Expr::class);

        $expressionBuilder
            ->expects(static::once())
            ->method('notIn')
            ->with('currencyMapping.merchantAccountId', ':merchantAccountIds');

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder
            ->expects(static::once())
            ->method('expr')
            ->willReturn($expressionBuilder);

        $queryBuilder
            ->expects(static::once())
            ->method('delete')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(static::once())
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(static::once())
            ->method('andWhere')
            ->with('currencyMapping.shop = :shop')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(static::exactly(2))
            ->method('setParameter')
            ->willReturnCallback(static function (string $key, mixed $value) use ($shop, $queryBuilder) {
                match ($key) {
                    'shop' => static::assertSame($shop, $value),
                    'merchantAccountIds' => static::assertEquals(['merchant-id-1', 'merchant-id-2'], $value),
                    default => static::fail('Unexpected parameter key: ' . $key),
                };

                return $queryBuilder;
            });

        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $queryBuilder
            ->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $query
            ->expects(static::once())
            ->method('execute');

        $this->connectionService
            ->expects(static::once())
            ->method('fromShop')
            ->willReturn($this->connectionService);

        $this->connectionService
            ->expects(static::once())
            ->method('getAllMerchantAccounts')
            ->willReturn($accounts);

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $this->validator->deleteInvalidCurrencyMappings($shop);
    }
}
