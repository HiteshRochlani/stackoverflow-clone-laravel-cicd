<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'votes_count' => 0,
            'best_answer_id' => null,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Question $question) {
            // Ensure slug is generated from title if not set
            if (!$question->slug) {
                $question->title = $question->title ?? $this->faker->sentence();
                $question->slug = Str::slug($question->title);
            }
        });
    }

    public function withBestAnswer()
    {
        return $this->state(function (array $attributes) {
            return [
                'best_answer_id' => Answer::factory()->create([
                    'question_id' => $attributes['id'] ?? null
                ])
            ];
        });
    }
}

