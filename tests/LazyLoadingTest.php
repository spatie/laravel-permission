<?php

namespace Spatie\Permission\Tests;

class LazyLoadingTest extends TestCase
{
    /** @test */
    public function test_it_does_not_violate_global_lazy_loading_rule()
    {
        $this->testUser->preventsLazyLoading = true;
        $this->testUser->wasRecentlyCreated = false;

        $this->expectNotToPerformAssertions();

        $this->testUser->assignRole('testRole');
    }
}
