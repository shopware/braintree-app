/* eslint-env node */
require('@rushstack/eslint-patch/modern-module-resolution')

module.exports = {
    root: true,
    extends: [
        'plugin:vue/vue3-recommended',
        'eslint:recommended',
        '@vue/eslint-config-typescript'
    ],
    parserOptions: {
        ecmaVersion: 'latest'
    },

    rules: {
        'no-undef': 'off',
        curly: ['error', 'multi-or-nest', 'consistent'],
        indent: 'off',
        eqeqeq: ['error', 'always'],
        semi: [2, 'always'],
        camelcase: ['error'],
        quotes: ['error', 'single', { avoidEscape: true, allowTemplateLiterals: true }],
        'max-len': 'off',
        'quote-props': ['error', 'as-needed'],
        'no-extra-semi': ['error'],
        'comma-dangle': ['error', 'always-multiline'],
        'no-console': ['warn', { allow: ['warn', 'error'] }],
        'spaced-comment': ['error', 'always'],
        'no-tabs': ['error'],
        'no-mixed-spaces-and-tabs': ['error'],
        'no-unused-vars': 'off',
        'no-useless-escape': ['error'],
        'no-multi-spaces': ['error'],
        'object-curly-spacing': ['error', 'always'],
        'space-before-function-paren': ['error', {
            anonymous: 'always',
            named: 'never',
            asyncArrow: 'always',
        }],
        'space-infix-ops': 'off',

        /* ts rules */
        '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_', caughtErrorsIgnorePattern: '^_' }],
        '@typescript-eslint/semi': ['error'],
        '@typescript-eslint/indent': ['error', 4],
        '@typescript-eslint/explicit-function-return-type': ['error', {
            allowTypedFunctionExpressions: true,
            allowHigherOrderFunctions: true,
            allowDirectConstAssertionInArrowFunctions: true,
            allowConciseArrowFunctionExpressionsStartingWithVoid: true,
            allowFunctionsWithoutTypeParameters: true,
        }],
        '@typescript-eslint/no-inferrable-types': 0,
        '@typescript-eslint/no-explicit-any': 0,
        '@typescript-eslint/no-empty-function': 0,
        '@typescript-eslint/no-non-null-assertion': 0,
        '@typescript-eslint/prefer-for-of': 'error',
        '@typescript-eslint/consistent-type-imports': ['error', {
            prefer: 'type-imports',
            disallowTypeAnnotations: true,
            fixStyle: 'separate-type-imports',
        }],
        '@typescript-eslint/member-delimiter-style': ['error', {
            multiline: {
                delimiter: 'comma',
                requireLast: true,
            },
            singleline: {
                delimiter: 'semi',
                requireLast: false,
            },
        }],
        '@typescript-eslint/space-infix-ops': 'error',
        /* ts rules */

        /* vue rules */
        'vue/order-in-components': ['error', {
            order: [
                'el', 'name', 'parent', 'functional',
                ['template', 'render'], 'inheritAttrs',
                ['provide', 'inject'], 'extends',
                'mixins', 'model', ['components', 'directives', 'filters'],
                'emits', ['props', 'propsData'], ['setup', 'created'], 'data',
                'metaInfo', 'computed', 'watch', 'LIFECYCLE_HOOKS',
                'methods', ['delimiters', 'comments'],
                'renderError',
            ],
        }],
        'vue/max-attributes-per-line': ['error', { singleline: 3 }],
        'vue/component-definition-name-casing': ['error', 'kebab-case'],
        'vue/require-explicit-emits': ['error'],
        'vue/block-lang': ['error', { script: { lang: 'ts' } }],
        'vue/html-indent': ['error', 4, { baseIndent: 0 }],
        'vue/html-quotes': ['error', 'single', { avoidEscape: true }],
        'vue/html-closing-bracket-newline': ['error', { singleline: 'never', multiline: 'always' }],
        'vue/component-tags-order': ['error', {
            order: [ ['script[setup]', 'template', 'script:not([setup])'] ],
        }],
        /* vue rules */
    },
}
