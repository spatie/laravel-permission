---
title: PhpStorm Interaction
weight: 7
---

# Extending PhpStorm 

You may wish to extend PhpStorm to support Blade Directives of this package.

1. In PhpStorm, open Preferences, and navigate to **Languages and Frameworks -> PHP -> Blade**
(File | Settings | Languages & Frameworks | PHP | Blade)
2. Uncheck "Use default settings", then click on the `Directives` tab.
3. Add the following new directives for the laravel-permission package:


**role**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasRole(`
- Suffix: `)); ?>`

--

**endrole**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**hasrole**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasRole(`
- Suffix: `)); ?>`

--

**endhasrole**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**hasanyrole**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasAnyRole(`
- Suffix: `)); ?>`

--

**endhasanyrole**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**hasallroles**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasAllRoles(`
- Suffix: `)); ?>`

--

**endhasallroles**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**unlessrole**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && !auth()->user()->hasRole(`
- Suffix: `)); ?>`

--

**endunlessrole**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--
