<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Integration\Command;

use Swag\Braintree\Tests\Command\TestManifestGenerateCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ManifestGenerateCommandTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testExecute(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);

        $command = $application->find('manifest:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => false]);

        $commandTester->assertCommandIsSuccessful();

        $path = $this->getManifest();

        if (!\file_exists($path)) {
            static::fail('Manifest file was not created');
        }

        $manifest = \simplexml_load_file($path);
        $appUrl = $_ENV['APP_URL'];
        $appSecret = $_ENV['APP_SECRET'];

        static::assertSame('SwagBraintreeApp', (string) $manifest->meta->name);
        static::assertSame('Braintree App', (string) $manifest->meta->label);
        static::assertSame('With the “PayPal Braintree” app developed by Shopware, you can now easily integrate one of the most popular solutions for credit card payments into your store.', (string) $manifest->meta->description);
        static::assertSame('shopware AG', (string) $manifest->meta->author);
        static::assertSame('(c) by shopware AG', (string) $manifest->meta->copyright);
        static::assertSame('Resources/plugin.webp', (string) $manifest->meta->icon);
        static::assertSame('MIT', (string) $manifest->meta->license);

        static::assertSame($appUrl . '/app/lifecycle/register', (string) $manifest->setup->registrationUrl);
        static::assertSame($appSecret, (string) $manifest->setup->secret);

        static::assertSame($appUrl . '/app/lifecycle/activate', (string) $manifest->webhooks->webhook[0]['url']);
        static::assertSame('app.activated', (string) $manifest->webhooks->webhook[0]['event']);
        static::assertSame($appUrl . '/app/lifecycle/deactivate', (string) $manifest->webhooks->webhook[1]['url']);
        static::assertSame('app.deactivated', (string) $manifest->webhooks->webhook[1]['event']);
        static::assertSame($appUrl . '/app/lifecycle/delete', (string) $manifest->webhooks->webhook[2]['url']);
        static::assertSame('app.deleted', (string) $manifest->webhooks->webhook[2]['event']);

        static::assertSame($appUrl . '/admin-sdk', (string) $manifest->admin->{'base-app-url'});
        static::assertSame($appUrl . '/api/gateway/checkout', (string) $manifest->gateways->checkout);

        static::assertSame(1, $manifest->payments->{'payment-method'}->count());
        static::assertSame('credit_card', (string) $manifest->payments->{'payment-method'}->identifier);
        static::assertSame('Credit or Debit Card (by Braintree)', (string) $manifest->payments->{'payment-method'}->name);
        static::assertSame('Resources/config/plugin.jpg', (string) $manifest->payments->{'payment-method'}->icon);

        static::assertSame('swag_braintree_app_product', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->name);
        static::assertSame('Braintree by PayPal', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->label);
        static::assertSame('product', $manifest->{'custom-fields'}->{'custom-field-set'}->{'related-entities'}->children()[0]->getName());
        static::assertSame('swag_braintree_app_commodity_code', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->fields->text->attributes()['name']);
        static::assertSame('1', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->fields->text->position);
        static::assertSame('e.g. 81162006', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->fields->text->placeholder);
        static::assertSame('United Nations Standard Products and Services Code (UNSPSC). Search https://www.unspsc.org/ for more.', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->fields->text->{'help-text'});
        static::assertSame('UNSPSC commodity code', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->fields->text->label);
        static::assertSame('true', (string) $manifest->{'custom-fields'}->{'custom-field-set'}->fields->text->{'allow-cart-expose'});
    }

    public function testCommandStructure(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);

        $command = $application->find('manifest:generate');

        static::assertSame('manifest:generate', $command->getName());
        static::assertSame('Generate the manifest.xml', $command->getDescription());
        static::assertSame('manifest:generate [-f|--force]', $command->getSynopsis());
        static::assertSame('Generates a manifest.xml from template, which is helpful during releases', $command->getHelp());
    }

    public function testWithoutForce(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);

        $command = $application->find('manifest:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => false]);

        $commandTester->assertCommandIsSuccessful();

        $path = $this->getManifest();

        static::assertFileExists($path);

        $commandTester->execute(['--force' => false]);
        $output = $commandTester->getDisplay();

        static::assertStringContainsString('manifest.xml already exists. Do you want to overwrite it?', $output);

        $commandTester->execute(['--force' => true]);
        $output = $commandTester->getDisplay();

        static::assertEmpty($output);

        $commandTester->assertCommandIsSuccessful();
    }

    public function testProdEnvironment(): void
    {
        self::bootKernel();

        $command = new TestManifestGenerateCommand(
            'https://foo-bar.com',
            '$ecretf0rt3st',
            'prod',
            static::getContainer()->getParameter('kernel.project_dir'),
            static::getContainer()->get('twig')
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => false]);

        $commandTester->assertCommandIsSuccessful();

        $path = $this->getManifest();

        static::assertFileExists($path);

        $manifest = \simplexml_load_file($path);

        static::assertSame('https://braintree.shopware.com/app/lifecycle/register', (string) $manifest->setup->registrationUrl);
        static::assertSame('https://braintree.shopware.com/app/lifecycle/activate', (string) $manifest->webhooks->webhook[0]['url']);
        static::assertSame('https://braintree.shopware.com/app/lifecycle/deactivate', (string) $manifest->webhooks->webhook[1]['url']);
        static::assertSame('https://braintree.shopware.com/app/lifecycle/delete', (string) $manifest->webhooks->webhook[2]['url']);
        static::assertSame('https://braintree.shopware.com/admin-sdk', (string) $manifest->admin->{'base-app-url'});
        static::assertSame('https://braintree.shopware.com/api/gateway/checkout', (string) $manifest->gateways->checkout);
    }

    private function getManifest(): string
    {
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');

        return $projectDir . '/var/cache/test/test_manifest.xml';
    }

    private function cleanup(): void
    {
        $manifest = $this->getManifest();

        if (\file_exists($manifest)) {
            \unlink($manifest);
        }
    }
}
