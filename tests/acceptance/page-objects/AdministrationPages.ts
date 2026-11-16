import { test as base } from '@playwright/test';

// eslint-disable-next-line @typescript-eslint/no-empty-object-type
export interface AdminPageTypes {
}

export const test = base.extend<FixtureTypes>({
});
