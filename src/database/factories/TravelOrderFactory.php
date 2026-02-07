<?php

namespace Database\Factories;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelOrder>
 */
class TravelOrderFactory extends Factory
{

    protected $model = TravelOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'requester_name' => $this->faker->name(),
            'destination' => $this->faker->city(),
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
            'status' => 'REQUESTED',
        ];
    }
}
