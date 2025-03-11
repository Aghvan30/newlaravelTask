<?php

namespace Tests\Feature;

use App\Http\Resources\UserResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

class TaskTest extends TestCase
{
//    use RefreshDatabase;

    protected $user;
    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create();
        $token = $this->user->createToken('TestToken')->plainTextToken;
        $this->headers = ['Authorization' => "Bearer $token"];
    }

    /** @test */
    public function it_fetches_all_tasks_for_authenticated_user()
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/tasks', $this->headers);
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }



    /** @test */
    public function it_creates_a_task()
    {
        $data = [
            'title' => 'Test Task',
            'description' => 'Task description'
        ];

        $response = $this->postJson('/api/tasks', $data, $this->headers);
        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Test Task');
    }

    /** @test */
    public function it_shows_a_task_belongs_to_the_user()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}", $this->headers);
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id);
    }

    /** @test */
    public function it_prevents_access_to_tasks_of_other_users()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/tasks/{$task->id}", $this->headers);
        $response->assertStatus(403);
    }

    /** @test */
    public function it_updates_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $updateData = ['title' => 'Updated Task', 'description' => 'Updated Description'];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData, $this->headers);
        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Task');
    }

//    /** @test */
    public function it_deletes_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}", [], $this->headers);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Задача удалена']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

}
