export default {
    root: true,
    extends: [
        '@roots/eslint-config',
        'plugin:@wordpress/eslint-plugin/recommended'
    ],
    rules: {
        'no-console': 'error',
    }
};