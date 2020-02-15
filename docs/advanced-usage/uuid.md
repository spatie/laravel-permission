---
title: UUID
weight: 6
---

If you're using UUIDs or GUIDs for your User models there are a few considerations which various users have contributed. As each UUID implementation approach is different, some of these may or may not benefit you. As always, your implementation may vary.

### Migrations
You will probably want to update the `create_permission_tables.php` migration:

- Replace `$table->unsignedBigInteger($columnNames['model_morph_key'])` with `$table->uuid($columnNames['model_morph_key'])`.


### Configuration (morph key)
You will probably also want to update the configuration `column_names.model_morph_key`:

- Change to `model_uuid` instead of the default `model_id`. (The default of `model_id` is shown in this snippet below. Change it to match your needs.)

        'column_names' => [    
            /*
             * Change this if you want to name the related model primary key other than
             * `model_id`.
             *
             * For example, this would be nice if your primary keys are all UUIDs. In
             * that case, name this `model_uuid`.
             */
            'model_morph_key' => 'model_id',
        ],

### Models
You will probably want to Extend the default Role and Permission models into your own namespace, to set some specific properties (see the Extending section of the docs):

- You may want to set `protected $keyType = "string";` so Laravel doesn't cast it to integer.
- You may want to set `protected $primaryKey = 'guid';` (or `uuid`, etc) if you changed the column name in your migrations.
- Optional: Some people have reported value in setting `public $incrementing = false;`, but others have said this caused them problems. Your implementation may vary.

### User Models
Troubleshooting tip: In the ***Prerequisites*** section of the docs we remind you that your User model must implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract so that the Gate features are made available to the User object.
In the default User model provided with Laravel, this is done by extending another model (aliased to `Authenticatable`), which extends the base Eloquent model. However, your UUID implementation may need to override that in order to set some of the properties mentioned in the Models section above. If you are running into difficulties, you may want to double-check whether your User model is doing UUIDs consistent with other parts of your app.
