const path = require('path');
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );


module.exports = {
    ...defaultConfig,
    entry: {
        "demo-plugin-frontend": [
            path.resolve(__dirname, 'resources/frontend/js/app.js'),
            path.resolve(__dirname, 'resources/frontend/scss/app.scss'),
        ],
        "demo-plugin-admin": [
            path.resolve(__dirname, 'resources/admin/js/app.js'),
            path.resolve(__dirname, 'resources/admin/scss/app.scss'),
        ]
    }
};
