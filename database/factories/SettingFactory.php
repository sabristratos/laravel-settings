<?php

namespace Strata\Settings\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Stratos\Settings\Models\Setting;

class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'group' => $this->faker->randomElement(['site', 'system', 'email', 'social', null]),
            'key' => $this->faker->unique()->slug(2),
            'value' => $this->faker->sentence(),
            'type' => 'string',
            'encrypted' => false,
        ];
    }

    /**
     * Indicate that the setting should be encrypted.
     */
    public function encrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'encrypted' => true,
        ]);
    }

    /**
     * Indicate that the setting should be of type integer.
     */
    public function integer(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->numberBetween(1, 100),
            'type' => 'int',
        ]);
    }

    /**
     * Indicate that the setting should be of type boolean.
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->boolean() ? '1' : '0',
            'type' => 'bool',
        ]);
    }

    /**
     * Indicate that the setting should be of type array/json.
     */
    public function array(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => json_encode($this->faker->words(5)),
            'type' => 'array',
        ]);
    }

    /**
     * Set a specific group for the setting.
     */
    public function group(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }
}
