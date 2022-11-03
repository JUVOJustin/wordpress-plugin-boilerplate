const { defineConfig } = require('cypress')

module.exports = defineConfig({
  fileServerFolder: 'tests/cypress',
  fixturesFolder: 'tests/cypress/fixtures',
  screenshotsFolder: 'tests/cypress/screenshots',
  videosFolder: 'tests/cypress/videos',
  downloadsFolder: 'tests/cypress/downloads',
  e2e: {
    setupNodeEvents(on, config) {
      console.log(config) // see everything in here!

      // modify config values
      if (!config.env.wp_username) {
        config.env.plugin_slug = 'demo-plugin'
        config.env.wp_username = 'admin';
        config.env.wp_password = 'password';
      }

      return config
    },
    baseUrl: 'http://localhost:8889',
    specPattern: 'tests/cypress/integration/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'tests/cypress/support/index.js',
    experimentalStudio: true,
  },
})
