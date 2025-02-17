<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'body' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'question_id' => Question::factory(),
            'votes_count' => 0,
        ];
    }
}
