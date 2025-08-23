---
description: Update strauss prefixing package from boilerplate
---

1. Get composer.json of the https://github.com/JUVOJustin/wordpress-plugin-boilerplate repository. 
2. Check scripts.prefix-namespaces in the composer.json
3. Extract the url from the script. It typically looks like: `https://github.com/BrianHenryIE/strauss/releases/download/0.22.2/strauss.phar` or `https://github.com/BrianHenryIE/strauss/releases/latest/download/strauss.phar`
4. Compare the version with the local version. 
5. If it is higher or "latest" replace the local version with the remote version. DO NOT ASK for confirmation for up to 2 versions
6. If the remote version is older ALWAYS ask the user
