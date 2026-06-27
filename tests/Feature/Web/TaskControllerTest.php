<?php


namespace Tests\Feature\Web;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function authenticated_user_can_view_tasks_index()
    {
        $this->actingAs($this->user);
        
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200)
                ->assertViewIs('tasks.index')
                ->assertViewHas('tasks');
    }

    /** @test */
    public function authenticated_user_can_view_create_task_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200)
                ->assertViewIs('tasks.create');
    }

    /** @test */
    public function authenticated_user_can_store_task()
    {
        $this->actingAs($this->user);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'due_date' => now()->addDays(5)->format('Y-m-d')
        ];

        $response = $this->post(route('tasks.store'), $taskData);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
    }

    /** @test */
    public function authenticated_user_can_view_task_show()
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('tasks.show', $task));

        $response->assertStatus(200)
                ->assertViewIs('tasks.show')
                ->assertViewHas('task');
    }

    /** @test */
    public function authenticated_user_can_view_edit_form()
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('tasks.edit', $task));

        $response->assertStatus(200)
                ->assertViewIs('tasks.edit')
                ->assertViewHas('task');
    }

    /** @test */
    public function authenticated_user_can_update_task()
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Task',
            'status' => 'in_progress'
        ];

        $response = $this->put(route('tasks.update', $task), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'Updated Task']);
    }

    /** @test */
    public function admin_can_delete_task_from_web()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $task = Task::factory()->create();

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_tasks()
    {
        $response = $this->get(route('tasks.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_cannot_edit_others_task()
    {
        $this->actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('tasks.edit', $task));

        $response->assertStatus(403);
    }
}