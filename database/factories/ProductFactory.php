<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'category_id' => \App\Models\Category::factory(),
            'name' => ucwords($name),
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomElement([15000, 20000, 25000, 30000]),
            'stock' => $this->faker->numberBetween(10, 100),
            'image' => null,
            'is_available' => true,
        ];
    }
}
