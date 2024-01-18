<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Payment;

use Braintree\ThreeDSecureInfo;

class ThreeDSecure
{
    public const ENROLLMENT_STATUS_YES = 'Y';
    public const ENROLLMENT_STATUS_NO = 'N';
    public const ENROLLMENT_STATUS_UNAVAILABLE = 'U';
    public const ENROLLMENT_STATUS_BYPASS = 'B';
    public const ENROLLMENT_STATUS_REQUEST_FAILURE = 'E';
    public const STATUS_AUTHENTICATE_ATTEMPT_SUCCESSFUL = 'authenticate_attempt_successful';
    public const STATUS_AUTHENTICATE_ERROR = 'authenticate_error';
    public const STATUS_AUTHENTICATE_FAILED = 'authenticate_failed';
    public const STATUS_AUTHENTICATE_SIGNATURE_VERIFICATION_FAILED = 'authenticate_signature_verification_failed';
    public const STATUS_AUTHENTICATE_SUCCESSFUL = 'authenticate_successful';
    public const STATUS_AUTHENTICATE_UNABLE_TO_AUTHENTICATE = 'authenticate_unable_to_authenticate';
    public const STATUS_AUTHENTICATION_UNAVAILABLE = 'authentication_unavailable';
    public const STATUS_LOOKUP_BYPASSED = 'lookup_bypassed';
    public const STATUS_LOOKUP_ENROLLED = 'lookup_enrolled';
    public const STATUS_LOOKUP_ERROR = 'lookup_error';
    public const STATUS_LOOKUP_NOT_ENROLLED = 'lookup_not_enrolled';
    public const STATUS_UNSUPPORTED_CARD = 'unsupported_card';
    public const STATUS_UNSUPPORTED_ACCOUNT_TYPE = 'unsupported_account_type';
    public const STATUS_UNSUPPORTED_THREE_D_SECURE_VERSION = 'unsupported_three_d_secure_version';
    public const STATUS_AUTHENTICATION_BYPASSED = 'authentication_bypassed';
    public const STATUS_CHALLENGE_REQUIRED = 'challenge_required';
    public const STATUS_AUTHENTICATE_REJECTED = 'authenticate_rejected';
    public const STATUS_AUTHENTICATE_FRICTIONLESS_FAILED = 'authenticate_frictionless_failed';
    public const STATUS_LOOKUP_FAILED_ACS_ERROR = 'lookup_failed_acs_error';
    public const STATUS_AUTHENTICATE_FAILED_ACS_ERROR = 'authenticate_failed_acs_error';
    public const STATUS_DATA_ONLY_SUCCESSFUL = 'data_only_successful';
    public const STATUS_LOOKUP_CARD_ERROR = 'lookup_card_error';
    public const STATUS_LOOKUP_SERVER_ERROR = 'lookup_server_error';
    public const STATUS_EXEMPTION_LOW_VALUE_SUCCESSFUL = 'exemption_low_value_successful';
    public const STATUS_EXEMPTION_TRA_SUCCESSFUL = 'exemption_tra_successful';
    public const STATUS_MPI_SERVER_ERROR = 'mpi_server_error';
    public const STATUS_SKIPPED_DUE_TO_RULE = 'skipped_due_to_rule';

    public static function isValid(ThreeDSecureInfo $info, bool $enforced): bool
    {
        return match ($info->status) {
            // enrolled cards, never reject
            self::STATUS_AUTHENTICATE_SUCCESSFUL,
            self::STATUS_AUTHENTICATION_BYPASSED => true,

            // enrolled cards, reject
            self::STATUS_AUTHENTICATE_ATTEMPT_SUCCESSFUL,
            self::STATUS_AUTHENTICATE_ERROR,
            self::STATUS_AUTHENTICATE_FAILED,
            self::STATUS_AUTHENTICATE_SIGNATURE_VERIFICATION_FAILED,
            self::STATUS_AUTHENTICATE_UNABLE_TO_AUTHENTICATE,
            self::STATUS_LOOKUP_ENROLLED,
            self::STATUS_CHALLENGE_REQUIRED,
            self::STATUS_DATA_ONLY_SUCCESSFUL,
            self::STATUS_AUTHENTICATE_REJECTED,
            self::STATUS_AUTHENTICATE_FRICTIONLESS_FAILED,
            self::STATUS_AUTHENTICATE_FAILED_ACS_ERROR,

            // errors, reject
            self::STATUS_LOOKUP_CARD_ERROR,
            self::STATUS_LOOKUP_SERVER_ERROR => $enforced === false,

            // errors, never reject
            self::STATUS_UNSUPPORTED_CARD,
            self::STATUS_UNSUPPORTED_ACCOUNT_TYPE,
            self::STATUS_UNSUPPORTED_THREE_D_SECURE_VERSION,
            self::STATUS_EXEMPTION_LOW_VALUE_SUCCESSFUL,
            self::STATUS_EXEMPTION_TRA_SUCCESSFUL,
            self::STATUS_MPI_SERVER_ERROR,
            self::STATUS_SKIPPED_DUE_TO_RULE,
            self::STATUS_LOOKUP_FAILED_ACS_ERROR,
            self::STATUS_AUTHENTICATION_UNAVAILABLE,
            self::STATUS_LOOKUP_BYPASSED,
            self::STATUS_LOOKUP_NOT_ENROLLED,
            self::STATUS_LOOKUP_ERROR => true,

            default => throw new \RuntimeException('Invalid status: ' . $info->status),
        };
    }

    public static function rejectIfEnforced(ThreeDSecureInfo $info): bool
    {
        if ($info->enrolled === null) {
            return false;
        }

        if ($info->enrolled === self::ENROLLMENT_STATUS_BYPASS) {
            return false;
        }

        if ($info->enrolled === self::ENROLLMENT_STATUS_NO) {
            return false;
        }

        if ($info->enrolled === self::ENROLLMENT_STATUS_UNAVAILABLE) {
            if ($info->status === self::STATUS_LOOKUP_FAILED_ACS_ERROR || $info->status === self::STATUS_AUTHENTICATION_UNAVAILABLE) {
                return false;
            }
        }

        if ($info->enrolled === self::ENROLLMENT_STATUS_YES && $info->liabilityShifted) {
            return false;
        }

        return true;
    }
}
