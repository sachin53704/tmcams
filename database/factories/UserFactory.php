<?php

namespace Database\Factories;

use App\Models\Clas;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Punch;
use App\Models\Shift;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => '1',
            'department_id' => Department::inRandomOrder()->first()->id,
            // 'sub_department_id' => Department::inRandomOrder()->first()->id,
            'emp_code' => fake()->unique()->numerify('######'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->unique()->phoneNumber(),
            'dob' => fake()->date(),
            'gender' => fake()->randomElement( array('m', 'f') ),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'shift_id' => Shift::inRandomOrder()->first()->id,
            'ward_id' => Ward::inRandomOrder()->first()->id,
            'clas_id' => Clas::inRandomOrder()->first()->id,
            'designation_id' => Designation::inRandomOrder()->first()->id,
            'doj' => fake()->date(),
            'is_ot' => fake()->randomElement( array('y', 'n') ),
            'is_divyang' => fake()->randomElement( array('y', 'n') ),
            'permanent_address' => fake()->sentence(),
            'present_address' => fake()->sentence(),
        ];
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
