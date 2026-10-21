<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_jwt_and_permission_allows_catalog_access(): void
    {
        User::factory()->create([
            'email' => 'reader@example.test',
            'password' => Hash::make('password123'),
            'permissions' => ['catalogo:read'],
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'reader@example.test',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->json('token');

        $this->withToken($token)
            ->getJson('/api/catalogo')
            ->assertOk()
            ->assertJsonPath('productos.0.nombre', 'Laptop Thunderobot');
    }

    public function test_catalog_requires_its_permission_claim(): void
    {
        $user = User::factory()->create(['permissions' => []]);
        $token = JWTAuth::fromUser($user);

        $this->withToken($token)
            ->getJson('/api/catalogo')
            ->assertForbidden()
            ->assertJsonPath('mensaje', 'Permiso insuficiente');
    }

    public function test_document_policy_allows_owners_and_blocks_other_users(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $document = Documento::create([
            'user_id' => $owner->id,
            'titulo' => 'Privado',
            'contenido' => 'Solo del propietario',
        ]);
        $otherDocument = Documento::create([
            'user_id' => $otherUser->id,
            'titulo' => 'Ajeno',
            'contenido' => 'No debe filtrarse',
        ]);
        $token = JWTAuth::fromUser($owner);

        $this->withToken($token)
            ->getJson("/api/documentos/{$document->id}")
            ->assertOk()
            ->assertJsonPath('contenido', 'Solo del propietario');

        $this->getJson("/api/documentos/{$otherDocument->id}")
            ->assertForbidden();
    }

    public function test_api_throttle_returns_429_after_ten_requests_per_minute(): void
    {
        $user = User::factory()->create(['permissions' => ['catalogo:read']]);
        $token = JWTAuth::fromUser($user);
        $this->withToken($token);

        for ($request = 1; $request <= 10; $request++) {
            $this->getJson('/api/catalogo')->assertOk();
        }

        $this->getJson('/api/catalogo')
            ->assertTooManyRequests();
    }

    public function test_cors_allows_a_configured_frontend_origin(): void
    {
        $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/catalogo')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }
}
