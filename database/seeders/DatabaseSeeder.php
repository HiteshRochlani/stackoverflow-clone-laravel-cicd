<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::create([
            'name' => 'OneStop',
            'email' => 'admin@onestopengineering.in',
            'password' => Hash::make('abcd1234')
        ]);

        \App\Models\User::factory(10)->create()->each(function(User $user) {
            $user->questions()->saveMany(
                Question::factory(random_int(2, 8))
                    ->make()
            )->each(function($question) {
                $question->answers()->saveMany(
                    Answer::factory(random_int(2, 10))->make()
                );
            });
        });
    }
}
