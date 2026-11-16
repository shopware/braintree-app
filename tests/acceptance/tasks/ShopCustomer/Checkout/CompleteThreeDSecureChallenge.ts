import { test as base } from '@playwright/test';

export type CompleteThreeDSecureChallengeTask = Task<[]>;

/**
 * Enters the one-time code the sandbox issuer shows in its challenge ("OTP: 1234"). Whether the
 * authentication then succeeds or fails is decided by the test card, not by the code.
 *
 * Uses plain fill() and click() for the same reason as FillBraintreeCard: the challenge is a cross-origin iframe.
 */
export const CompleteThreeDSecureChallenge = base.extend<FixtureTypes>({
    CompleteThreeDSecureChallenge: async ({ StorefrontBraintreeCardPayment }, use) => {
        const task: CompleteThreeDSecureChallengeTask = () => {
            return async function CompleteThreeDSecureChallenge() {
                await StorefrontBraintreeCardPayment.threeDSecureChallengeCodeInput.fill('1234');
                await StorefrontBraintreeCardPayment.threeDSecureChallengeSubmitButton.click();
            };
        };

        await use(task);
    },
});
