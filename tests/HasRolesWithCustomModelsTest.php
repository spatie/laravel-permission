<?php

namespace Spatie\Permission\Test;

class HasRolesWithCustomModelsTest extends HasRolesTest
{
    /** @var bool */
    protected $useCustomModels = true;

    /** @test */
    public function it_can_use_custom_model_role(): void
    {
        $this->assertSame(get_class($this->testUserRole), Role::class);
    }
}
