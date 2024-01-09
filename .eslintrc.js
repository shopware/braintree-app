/* eslint-env node */
module.exports = {
    root: true,
    plugins: ['vue', '@typescript-eslint'],
    parser: 'vue-eslint-parser',
    parserOptions: {
        parser: '@typescript-eslint/parser',
        sourceType: 'module',
        project: './tsconfig.json',
        extraFileExtensions: ['.vue'],
    },
    env: {
        browser: true,
    },
    extends: [
        'eslint:recommended',
        'plugin:@typescript-eslint/recommended',
        'plugin:@typescript-eslint/recommended-requiring-type-checking',
        'plugin:vue/recommended',
    ],
    overrides: [
        {
            files: ['*.ts'],
            parser: '@typescript-eslint/parser',
            parserOptions: {
                sourceType: 'module',
                project: './tsconfig.json',
            },
        },
    ],
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
};
