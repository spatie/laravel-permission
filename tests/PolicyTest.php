<?php

use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\Tests\ContentPolicy;
use Spatie\Permission\Tests\TestCase;
use Spatie\Permission\Tests\TestModels\Content;

uses(TestCase::class);

it('policy methods and before intercepts can allow and deny', function () {
    $record1 = Content::create(['content' => 'special admin content']);
    $record2 = Content::create(['content' => 'viewable', 'user_id' => $this->testUser->id]);

    app(Gate::class)->policy(Content::class, ContentPolicy::class);

    expect($this->testUser->can('view', $record1))->toBeFalse();
    expect($this->testUser->can('update', $record1))->toBeFalse();

    expect($this->testUser->can('update', $record2))->toBeTrue();

    // test that the Admin cannot yet view 'special admin content', because doesn't have Role yet
    expect($this->testAdmin->can('update', $record1))->toBeFalse();

    $this->testAdmin->assignRole($this->testAdminRole);
    // test that the Admin can view 'special admin content'
    expect($this->testAdmin->can('update', $record1))->toBeTrue();
    expect($this->testAdmin->can('update', $record2))->toBeTrue();
});
