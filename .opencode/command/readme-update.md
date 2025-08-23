---
description: Update or create the README.md file for the WordPress plugin. 
---

1. Review the content of the upstream README.md file: <upstream_readme> README.md </upstream_readme>

2. Examine the current content of the local README.md file (if it exists): <local_readme> {{LOCAL_README}} </local_readme>

3. Analyze the plugin files: <plugin_files> {{PLUGIN_FILES}} </plugin_files>

4. Analyze the upstream README
* Identify information specific to the initial setup or boilerplate.
* Note any best-practice implementations mentioned.
* Identify generally applicable information such as structure, commands, and coding rules.

5. Analyze the local README
* Identify any existing plugin-specific information.
* Note any outdated or irrelevant information.
* Analyze the plugin files:

6. Determine the scope of the plugin
* Identify specific functionalities built into the plugin.
* Look for integrations with other plugins.
* Identify any REST API endpoints.
* Look for new CLI commands.
* Identify any shortcodes.
* Identify Gutenberg Blocks.
* Note any registered Post Types and Taxonomies, and check if they have a model representation in PHP.

7. Update the local README
* Remove any setup instructions specific to the boilerplate.
* Include generally applicable information from the upstream README (structure, commands, coding rules, etc.).
* Add or update information about: a. The scope of the plugin b. Specific functionalities c. Plugin integrations d. * REST API endpoints e. New CLI commands f. Shortcodes g. Registered Post Types, Taxonomies, and their model representations

8. Format your final output
* Present the updated README content.
* Ensure the content is well-structured and easy to read.

Your final output should only include the updated README content. Do not include any of your analysis or thought process in the final output.