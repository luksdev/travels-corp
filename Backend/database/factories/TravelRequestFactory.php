<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelRequest>
 */
class TravelRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDate = $this->faker->dateTimeBetween('now', '+6 months');
        $returnDate    = $this->faker->dateTimeBetween($departureDate, $departureDate->format('Y-m-d') . ' +14 days');

        return [
            'user_id'     => User::factory(),
            'destination' => $this->faker->randomElement([
                'São Paulo, SP',
                'Rio de Janeiro, RJ',
                'Belo Horizonte, MG',
                'Salvador, BA',
                'Brasília, DF',
                'Fortaleza, CE',
                'Curitiba, PR',
                'Recife, PE',
                'Porto Alegre, RS',
                'Goiânia, GO',
                'Miami, FL',
                'New York, NY',
                'London, UK',
                'Paris, France',
                'Tokyo, Japan',
            ]),
            'departure_date' => $departureDate->format('Y-m-d'),
            'return_date'    => $returnDate->format('Y-m-d'),
            'status'         => $this->faker->randomElement(['requested', 'approved', 'cancelled']),
        ];
    }

    /**
     * Requested status state
     */
    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'requested',
        ]);
    }

    /**
     * Approved status state
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Cancelled status state
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
