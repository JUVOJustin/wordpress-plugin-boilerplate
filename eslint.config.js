export default {
    root: true,
    extends: [
        '@roots/eslint-config',
        'plugin:@wordpress/eslint-plugin/recommended'
    ],
    rules: {
        'no-console': 'error',
        camelcase: ['error', {
            allow: ['^demo_plugin'] // Allow global with plugin name to not be camelCased
        }],
    },
    globals: {
        "jQuery": "readonly",
        "$": "readonly",
        "demo_plugin": "readonly"
    }
};
