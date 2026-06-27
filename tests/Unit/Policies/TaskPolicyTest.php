<?php

namespace Tests\Unit\Policies;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected TaskPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy();
    }

    /** @test */
    public function admin_can_view_any_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $this->assertTrue($this->policy->viewAny($admin));
    }

    /** @test */
    public function admin_can_view_any_specific_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        $this->assertTrue($this->policy->view($admin, $task));
    }

    /** @test */
    public function user_can_view_own_task()
    {
        $user = User::factory()->create(['role' => 'user']);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $task));
    }

    /** @test */
    public function user_cannot_view_others_task()
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($this->policy->view($user, $task));
    }

    /** @test */
    public function admin_can_update_any_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        $this->assertTrue($this->policy->update($admin, $task));
    }

    /** @test */
    public function user_can_update_own_task()
    {
        $user = User::factory()->create(['role' => 'user']);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $task));
    }

    /** @test */
    public function admin_can_delete_any_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $task));
    }

    /** @test */
    public function user_cannot_delete_task()
    {
        $user = User::factory()->create(['role' => 'user']);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->delete($user, $task));
    }

    /** @test */
    public function admin_can_view_statistics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $this->assertTrue($this->policy->viewStatistics($admin));
    }

    /** @test */
    public function user_can_view_statistics()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $this->assertTrue($this->policy->viewStatistics($user));
    }
}