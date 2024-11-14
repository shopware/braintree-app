<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @see https://github.com/doctrine/migrations/issues/1406
 */
#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
#[When(env: 'dev'), When(env: 'test')]
class DoctrineSchemaSubscriber
{
    private readonly TableMetadataStorageConfiguration $configuration;

    public function __construct(
        #[Autowire(service: 'doctrine.migrations.dependency_factory')]
        private readonly DependencyFactory $dependencyFactory,
    ) {
        $configuration = $this->dependencyFactory->getConfiguration()->getMetadataStorageConfiguration();

        \assert($configuration instanceof TableMetadataStorageConfiguration);

        $this->configuration = $configuration;
    }

    /**
     * @see \Doctrine\Migrations\Metadata\Storage\TableMetadataStorage::getExpectedTable
     *
     * Adds doctrine/migration metadata table to the schema.
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        $schemaChangelog = $schema->createTable($this->configuration->getTableName());
        $schemaChangelog->addColumn(
            $this->configuration->getVersionColumnName(),
            'string',
            ['notnull' => true, 'length' => $this->configuration->getVersionColumnLength()],
        );
        $schemaChangelog->addColumn($this->configuration->getExecutedAtColumnName(), 'datetime', ['notnull' => false]);
        $schemaChangelog->addColumn($this->configuration->getExecutionTimeColumnName(), 'integer', ['notnull' => false]);
        $schemaChangelog->setPrimaryKey([$this->configuration->getVersionColumnName()]);
    }
}
