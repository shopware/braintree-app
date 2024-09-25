<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @codeCoverageIgnore - This class is only used in dev mode
 */
class HmrService
{
    public function __construct(
        #[Autowire(param: 'kernel.environment')]
        private ?string $environment,
        #[Autowire(env: 'int:VITE_PORT')]
        private ?int $vitePort,
    ) {
    }

    public function isHmr(): bool
    {
        if ($this->environment !== 'dev') {
            return false;
        }

        if (!$this->vitePort) {
            return false;
        }

        $connection = @\fsockopen('localhost', $this->vitePort);

        if (!\is_resource($connection)) {
            return false;
        }

        \fclose($connection);

        return true;
    }
}
