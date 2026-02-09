<?php

use Spatie\Permission\Tests\TestSupport\TestModels\Role;

beforeEach(function () {
    $this->setUpRoleNesting();

    $this->parent_roles = [
        'has_no_children' => Role::create(['name' => 'has_no_children']),
        'has_1_child' => Role::create(['name' => 'has_1_child']),
        'has_3_children' => Role::create(['name' => 'has_3_children']),
    ];
    $this->child_roles = [
        'has_no_parents' => Role::create(['name' => 'has_no_parents']),
        'has_1_parent' => Role::create(['name' => 'has_1_parent']),
        'has_2_parents' => Role::create(['name' => 'has_2_parents']),
        'third_child' => Role::create(['name' => 'third_child']),
    ];

    $this->parent_roles['has_1_child']->children()->attach($this->child_roles['has_2_parents']);
    $this->parent_roles['has_3_children']->children()->attach([
        $this->child_roles['has_2_parents']->getKey(),
        $this->child_roles['has_1_parent']->getKey(),
        $this->child_roles['third_child']->getKey(),
    ]);
});

it('returns correct withCount of nested roles', function (string $role_group, string $index, string $relation, int $expectedCount) {
    $role = $this->$role_group[$index];
    $count_field_name = sprintf('%s_count', $relation);

    $actualCount = (int) Role::withCount($relation)->find($role->getKey())->$count_field_name;

    expect($actualCount)->toBe($expectedCount, sprintf('%s expects %d %s, %d found', $role->name, $expectedCount, $relation, $actualCount));
})->with([
    ['parent_roles', 'has_no_children', 'children', 0],
    ['parent_roles', 'has_1_child', 'children', 1],
    ['parent_roles', 'has_3_children', 'children', 3],
    ['child_roles', 'has_no_parents', 'parents', 0],
    ['child_roles', 'has_1_parent', 'parents', 1],
    ['child_roles', 'has_2_parents', 'parents', 2],
]);
