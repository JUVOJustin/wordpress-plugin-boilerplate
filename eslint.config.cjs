const wordpressConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...wordpressConfig,
	{
		languageOptions: {
			globals: {
				demo_plugin: 'readonly',
			},
		},
		rules: {
			'no-console': 'warn',
			camelcase: [
				'error',
				{
					allow: [ '^demo_plugin' ],
				},
			],
		},
	},
];
