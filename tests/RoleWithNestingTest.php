<?php

namespace Spatie\Permission\Test;

class RoleWithNestingTest extends TestCase
{
    private static $old_migration;
    /**
     * @var RoleWithNesting[]
     */
    protected $parent_roles=[];
    /**
     * @var RoleWithNesting[]
     */
    protected $child_roles=[];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$old_migration = self::$migration;
        self::$migration = self::getMigration();
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->parent_roles = [];
        $this->child_roles = [];
        $this->parent_roles["has_no_children"] = RoleWithNesting::create(["name"=>"has_no_children"]);
        $this->parent_roles["has_1_child"] = RoleWithNesting::create(["name"=>"has_1_child"]);
        $this->parent_roles["has_3_children"] = RoleWithNesting::create(["name"=>"has_3_children"]);

        $this->child_roles["has_no_parents"] = RoleWithNesting::create(["name"=>"has_no_parents"]);
        $this->child_roles["has_1_parent"] = RoleWithNesting::create(["name"=>"has_1_parent"]);
        $this->child_roles["has_2_parents"] = RoleWithNesting::create(["name"=>"has_2_parents"]);
        $this->child_roles["third_child"] = RoleWithNesting::create(["name"=>"third_child"]);

        $this->parent_roles["has_1_child"]->children()->attach($this->child_roles["has_2_parents"]);
        $this->parent_roles["has_3_children"]->children()->attach($this->child_roles["has_2_parents"]);
        $this->parent_roles["has_3_children"]->children()->attach($this->child_roles["has_1_parent"]);
        $this->parent_roles["has_3_children"]->children()->attach($this->child_roles["third_child"]);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$migration = self::$old_migration;
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('permission.models.role', RoleWithNesting::class);
        $app['config']->set('permission.table_names.roles', "nesting_role");
    }

    protected static function getMigration()
    {
        require_once __DIR__."/customMigrations/roles_with_nesting_migration.php.stub";
        return new \CreatePermissionTablesWithNested();
    }

    /** @test
     * @dataProvider roles_list
     */
    public function it_returns_correct_withCount_of_nested_roles($role_group,$index,$relation,$expectedCount)
    {
        $role = $this->$role_group[$index];
        $count_field_name = sprintf("%s_count", $relation);

        $actualCount = intval(RoleWithNesting::query()->withCount($relation)->find($role->id)->$count_field_name);

        $this->assertSame(
            $expectedCount,
            $actualCount,
            sprintf("%s expects %d %s, %d found",$role->name,$expectedCount,$relation,$actualCount)
        );
    }

    public function roles_list(){
        return [
            ["parent_roles","has_no_children","children",0],
            ["parent_roles","has_1_child","children",1],
            ["parent_roles","has_3_children","children",3],
            ["child_roles","has_no_parents","parents",0],
            ["child_roles","has_1_parent","parents",1],
            ["child_roles","has_2_parents","parents",2],
        ];
    }
}
