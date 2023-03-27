/**
 * Build configuration for bud.js
 * @param {import('@roots/bud').Bud} bud
 */
export default async bud => {
    /**
     * The bud.js instance
     */
    bud

        /**
         * Set the project source directory
         */
        .setPath(`@src`, `resources`)

        /**
         * Set the application entrypoints
         * These paths are expressed relative to the `@src` directory
         */
        .entry({
            "frontend": [`/frontend/js/app.js`, `/frontend/scss/app.scss`], // [`./sources/app.js`, `./sources/app.css`]
            "admin": [`/admin/js/app.js`, `/admin/scss/app.scss`]
        })

        .provide({
            jquery: ['$', 'jQuery'],
        })

        /**
         * Copy static assets from `sources/static` to `dist/static`
         */
        .assets({
            from: bud.path(`@src/static`),
            to: bud.path(`@dist/static`),
            noErrorOnMissing: true,
        })
        .splitChunks()
        .minimize(bud.isProduction)
        .proxy(false) // Disable since we are using ddev
}