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

class AuthСontrollerTest extends TestCase
{
    use RefreshDatabase;
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
    public function register_returns_user_object()
    {
        $request = $this->json( 'POST', '/api/register', [
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John',
            'email' => 'john@example.com',
        ]);

        $request->assertStatus(201);

    }

    /** @test */
    public function login_returns_token()
    {
        $request = $this->json( 'POST', '/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $request->assertStatus(200)->assertJsonStructure([
            'token',
        ]);
    }
}
