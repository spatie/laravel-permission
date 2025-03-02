<?php

namespace Spatie\Permission\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Tests\TestModels\Role;

class RoleWithNestingTest extends TestCase
{
    /** @var bool */
    protected $useCustomModels = true;

    /** @var Role[] */
    protected array $parent_roles = [];

    /** @var Role[] */
    protected array $child_roles = [];

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    /**
     * Set up the database.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function setUpDatabase($app)
    {
        parent::setUpDatabase($app);

        $tableRoles = $app['config']->get('permission.table_names.roles');

        $app['db']->connection()->getSchemaBuilder()->create(Role::HIERARCHY_TABLE, function ($table) use ($tableRoles) {
            $table->id();
            $table->uuid('parent_id');
            $table->uuid('child_id');
            $table->foreign('parent_id')->references('role_test_id')->on($tableRoles);
            $table->foreign('child_id')->references('role_test_id')->on($tableRoles);
        });
    }

    /** @test
     * @dataProvider roles_list
     */
    #[DataProvider('roles_list')]
    #[Test]
    public function it_returns_correct_withCount_of_nested_roles($role_group, $index, $relation, $expectedCount)
    {
        $role = $this->$role_group[$index];
        $count_field_name = sprintf('%s_count', $relation);

        $actualCount = (int) Role::withCount($relation)->find($role->getKey())->$count_field_name;

        $this->assertSame(
            $expectedCount,
            $actualCount,
            sprintf('%s expects %d %s, %d found', $role->name, $expectedCount, $relation, $actualCount)
        );
    }

    public static function roles_list()
    {
        return [
            ['parent_roles', 'has_no_children', 'children', 0],
            ['parent_roles', 'has_1_child', 'children', 1],
            ['parent_roles', 'has_3_children', 'children', 3],
            ['child_roles', 'has_no_parents', 'parents', 0],
            ['child_roles', 'has_1_parent', 'parents', 1],
            ['child_roles', 'has_2_parents', 'parents', 2],
        ];
    }
}
