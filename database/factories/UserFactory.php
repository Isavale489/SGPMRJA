<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\User;
use App\Models\UserRecoveryQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => fn () => Rol::where('nombre', 'Administrador')->value('id'),
            'estado' => 1,
        ];
    }

    /**
     * Por defecto el usuario nace "en operación": con sus 3 preguntas de
     * seguridad configuradas, para que EnsureRecoveryQuestionsConfigured no
     * lo desvíe a /profile. Usar sinPreguntasSeguridad() para probar ese desvío.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            foreach ([1, 2, 3] as $orden) {
                UserRecoveryQuestion::create([
                    'user_id' => $user->id,
                    'pregunta_id' => $orden,
                    'respuesta' => Hash::make('respuesta'),
                    'orden' => $orden,
                ]);
            }
        });
    }

    /**
     * Usuario sin preguntas de seguridad (primer ingreso).
     */
    public function sinPreguntasSeguridad(): static
    {
        return $this->afterCreating(fn (User $user) => $user->recoveryQuestions()->delete());
    }

    /**
     * Usuario con el rol de sistema Supervisor (gobernado por permiso_rol).
     */
    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Rol::where('nombre', 'Supervisor')->value('id'),
        ]);
    }

    /**
     * Usuario inhabilitado (estado = 0); el middleware active.user lo expulsa.
     */
    public function inhabilitado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 0,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
