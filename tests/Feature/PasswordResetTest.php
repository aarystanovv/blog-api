<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_request_password_reset_link()
    {
        // Создаем тестового пользователя
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Отправляем запрос на сброс пароля
        $response = $this->postJson('/api/password/forgot', [
            'email' => 'test@example.com',
        ]);

        // Проверяем, что запрос прошел успешно
        $response->assertStatus(200)
            ->assertJson(['message' => 'We have emailed your password reset link!']);
    }

    /** @test */
    public function password_reset_link_can_be_used_to_reset_password()
    {
        // Создаем тестового пользователя
        $user = User::factory()->create(['email' => 'test@example.com']);
        // Генерируем токен сброса пароля
        $token = Password::createToken($user);

        // Отправляем запрос на сброс пароля с использованием токена
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        // Проверяем, что запрос прошел успешно
        $response->assertStatus(200)
            ->assertJson(['message' => 'Your password has been reset!']);

        // Проверяем, что пароль действительно изменен
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    /** @test */
    public function reset_password_fails_with_invalid_token()
    {
        // Создаем тестового пользователя
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Пытаемся сбросить пароль с некорректным токеном
        $response = $this->postJson('/api/password/reset', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        // Проверяем, что сброс пароля не удался
        $response->assertStatus(400)
            ->assertJson(['email' => ['This password reset token is invalid.']]);
    }
}
