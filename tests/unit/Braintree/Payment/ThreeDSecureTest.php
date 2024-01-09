<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Payment;

use Braintree\ThreeDSecureInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Payment\ThreeDSecure;

#[CoversClass(ThreeDSecure::class)]
class ThreeDSecureTest extends TestCase
{
    #[DataProvider('threeDSecureInfoProvider')]
    public function testIsEnforced(
        string $status,
        bool $liabilityShiftPossible,
        bool $liabilityShifted,
        bool $rejectIfEnforced,
        ?string $enrolled
    ): void {
        $info = ThreeDSecureInfo::factory([
            'status' => $status,
            'liabilityShiftPossible' => $liabilityShiftPossible,
            'liabilityShifted' => $liabilityShifted,
            'enrolled' => $enrolled,
        ]);

        static::assertSame($rejectIfEnforced, ThreeDSecure::rejectIfEnforced($info));
    }

    #[DataProvider('threeDSecureInfoProvider')]
    public function testIsValid(
        string $status,
        bool $liabilityShiftPossible,
        bool $liabilityShifted,
        bool $rejectIfEnforced,
        ?string $enrolled
    ): void {
        $info = ThreeDSecureInfo::factory([
            'status' => $status,
            'liabilityShiftPossible' => $liabilityShiftPossible,
            'liabilityShifted' => $liabilityShifted,
            'enrolled' => $enrolled,
        ]);

        static::assertSame(!$rejectIfEnforced, ThreeDSecure::isValid($info, true));
    }

    public function testUnknownStatus(): void
    {
        $info = ThreeDSecureInfo::factory([
            'status' => 'foo',
            'liabilityShiftPossible' => true,
            'liabilityShifted' => true,
            'enrolled' => 'Y',
        ]);

        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Invalid status: foo');

        ThreeDSecure::isValid($info, false);
    }

    public static function threeDSecureInfoProvider(): \Generator
    {
        // enrolled cards, never reject
        yield ['authenticate_successful', true, true, false, 'Y'];
        yield ['authentication_bypassed', false, true, false, 'Y'];

        // enrolled cards, reject
        yield ['authenticate_attempt_successful', true, false, true, 'Y'];
        yield ['authenticate_error', true, false, true, 'Y'];
        yield ['authenticate_failed', true, false, true, 'Y'];
        yield ['authenticate_signature_verification_failed', true, false, true, 'Y'];
        yield ['authenticate_unable_to_authenticate', true, false, true, 'Y'];
        yield ['lookup_enrolled', true, false, true, 'Y'];
        yield ['challenge_required', true, false, true, 'Y'];
        yield ['data_only_successful', false, false, true, 'Y'];
        yield ['authenticate_rejected', false, false, true, 'Y'];
        yield ['authenticate_frictionless_failed', false, false, true, 'Y'];
        yield ['authenticate_failed_acs_error', false, false, true, 'Y'];

        // errors, reject
        yield ['lookup_card_error', false, false, true, 'U'];
        yield ['lookup_server_error', false, false, true, 'U'];

        // errors, never reject
        yield ['unsupported_card', false, false, false, null];
        yield ['unsupported_account_type', false, false, false, null];
        yield ['unsupported_three_d_secure_version', false, false, false, null];
        yield ['exemption_low_value_successful', false, false, false, null];
        yield ['exemption_tra_successful', false, false, false, null];
        yield ['mpi_server_error', false, false, false, null];
        yield ['skipped_due_to_rule', false, false, false, null];
        yield ['lookup_failed_acs_error', false, false, false, 'U'];
        yield ['authentication_unavailable', false, false, false, 'U'];
        yield ['lookup_bypassed', false, false, false, 'B'];
        yield ['lookup_not_enrolled', false, false, false, 'N'];
        yield ['lookup_error', false, false, false, null];
    }
}
