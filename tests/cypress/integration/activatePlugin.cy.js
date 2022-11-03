describe('Plugin', () => {
    before(() => {
        // cy.wpCli('plugin deactivate cmb2', { failOnNonZeroExit: false })
    })

    beforeEach(() => {
        cy.logIn()
        cy.visit('/wp-admin/plugins.php')
        cy.location('pathname').should('equal', '/wp-admin/plugins.php')
    })

    it('Can be deactivated', () => {
        cy.get('#deactivate-' + Cypress.env('plugin_slug')).click()
        cy.get('#activate-' + Cypress.env('plugin_slug')).should('be.visible')
    })

    it('Can be activated', () => {
        cy.get('#activate-' + Cypress.env('plugin_slug')).click()
        cy.get('#deactivate-' + Cypress.env('plugin_slug')).should('be.visible')
    })
})