---
description: Upsert Q&A tools from the boilerplate
---

Bring this project (currently on an older snapshot of the boilerplate) in line with the latest upstream coding‑standard and CI configuration.

# Authoritative sources
Download these files verbatim and treat them as canonical:
- phpcs.xml
- phpstan.neon
- composer.json
- .github/workflows/test-analyse.yml
  (Direct raw URLs)
  https://raw.githubusercontent.com/JUVOJustin/wordpress-plugin-boilerplate/refs/heads/main/phpcs.xml
  https://raw.githubusercontent.com/JUVOJustin/wordpress-plugin-boilerplate/refs/heads/main/phpstan.neon
  https://raw.githubusercontent.com/JUVOJustin/wordpress-plugin-boilerplate/refs/heads/main/composer.json
  https://raw.githubusercontent.com/JUVOJustin/wordpress-plugin-boilerplate/refs/heads/main/.github/workflows/test-analyse.yml

# Tasks
1. **Synchronise configuration**
   • Replace or merge the local counterparts with the downloaded versions, preserving unique local settings but preferring upstream on conflicts.
2. **Composer**
   • Ensure `require-dev` lists all tools referenced by the new configs (phpcs/phpcbf, phpstan, custom sniffs, etc.).
   • Copy every relevant `scripts` entry from the upstream `composer.json` so that shortcuts like `composer phpcs`, `composer phpcbf`, and `composer phpstan` work identically in this repository.
   • Where a package already exists, upgrade it to the version required upstream.
3. **GitHub Actions**
   • In `test-analyse.yml`, add a step that runs `composer phpcs -- --standard=phpcs.xml` and fails on coding‑standard **errors**.
   • Leave the existing matrix/cache/setup logic untouched.
4. **Validation**
   • From a clean checkout, the commands below must exit:
     ```bash
     composer install
     composer phpcs
     composer phpstan
     ```
5. **Execute quality tools**
   • After setup, run each Composer script copied in step2 to confirm they behave as expected:
     ```bash
     composer phpcs
     composer phpcbf
     composer phpstan
     ```
   • Ensure these scripts are also executed within the CI workflow (where appropriate) and finish without errors.                                                                                                              