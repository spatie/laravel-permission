<?php

namespace Spatie\Permission\Tests;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Gate;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Tests\TestModels\Content;

class PolicyTest extends TestCase
{
    /** @test */
    #[Test]
    public function policy_methods_and_before_intercepts_can_allow_and_deny()
    {
        $record1 = Content::create(['content' => 'special admin content']);
        $record2 = Content::create(['content' => 'viewable', 'user_id' => $this->testUser->id]);

        app(Gate::class)->policy(Content::class, ContentPolicy::class);

        $this->assertFalse($this->testUser->can('view', $record1)); // policy rule for 'view'
        $this->assertFalse($this->testUser->can('update', $record1)); // policy rule for 'update'

        $this->assertTrue($this->testUser->can('update', $record2)); // policy rule for 'update' when matching user_id

        // test that the Admin cannot yet view 'special admin content', because doesn't have Role yet
        $this->assertFalse($this->testAdmin->can('update', $record1));

        $this->testAdmin->assignRole($this->testAdminRole);
        // test that the Admin can view 'special admin content'
        $this->assertTrue($this->testAdmin->can('update', $record1)); // policy override via 'before'
        $this->assertTrue($this->testAdmin->can('update', $record2)); // policy override via 'before'
    }
}
class ContentPolicy
{
    public function before(Authorizable $user, string $ability): ?bool
    {
        return $user->hasRole('testAdminRole', 'admin') ?: null;
    }

    public function view($user, $content)
    {
        return $user->id === $content->user_id;
    }

    public function update($user, $modelRecord): bool
    {
        return $user->id === $modelRecord->user_id || $user->can('edit-articles');
    }
}
