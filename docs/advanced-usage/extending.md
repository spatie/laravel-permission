---
title: Extending
weight: 3
---


If you are extending or replacing the role/permission models, you will need to specify your new models in the configuration. 
First be sure that you've published the configuration file (see the Installation instructions), and edit it to update the `models.role` and `models.permission` values to point to your new models.

Note the following requirements when extending/replacing the models: 


### Extending
If you need to EXTEND the existing `Role` or `Permission` models note that:

- Your `Role` model needs to extend the `Spatie\Permission\Models\Role` model
- Your `Permission` model needs to extend the `Spatie\Permission\Models\Permission` model

### Replacing
If you need to REPLACE the existing `Role` or `Permission` models you need to keep the
following things in mind:

- Your `Role` model needs to implement the `Spatie\Permission\Contracts\Role` contract
- Your `Permission` model needs to implement the `Spatie\Permission\Contracts\Permission` contract

