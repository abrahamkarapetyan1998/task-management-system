<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskService $taskService;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function it_can_create_a_task_without_conflicts()
    {
        Sanctum::actingAs($this->user, ['*']);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Test description',
            'user_id' => $this->user->id,
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'pending',
        ];

        $response = $this->json('POST', '/api/tasks', $taskData);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response->assertStatus(201);
    }

    public function it_fails_when_task_overlaps_existing_task()
    {
        Sanctum::actingAs($this->user, ['*']);

        $now = Carbon::now();
        Carbon::setTestNow($now);

        Task::factory()->create([
            'title' => 'Existing Task',
            'user_id' => $this->user->id,
            'start_date' => $now->addHour(),
            'end_date' => $now->copy()->addHours(2),
        ]);

        $taskData = [
            'title' => 'Overlapping Task',
            'user_id' => $this->user->id,
            'start_date' => $now->copy()->addHour(),
            'end_date' => $now->copy()->addHours(2),
        ];

        $response = $this->json('POST', '/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertSee('User already has a task in this time range.');
    }
}
