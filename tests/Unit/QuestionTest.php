<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class QuestionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_slug_from_title()
    {
        $question = Question::factory()->create(['title' => 'Test Question']);

        $this->assertEquals('test-question', $question->slug);
    }

    /** @test */
    public function it_belongs_to_an_owner()
    {
        $question = Question::factory()->create();

        $this->assertInstanceOf(User::class, $question->owner);
    }

    /** @test */
    public function it_has_many_answers()
    {
        $question = Question::factory()->create();
        Answer::factory()->create(['question_id' => $question->id]);

        $this->assertInstanceOf(Answer::class, $question->answers->first());
    }

    /** @test */
    public function it_has_url_accessor()
    {
        $question = Question::factory()->create(['title' => 'Test Question']);

        $this->assertEquals('/questions/test-question', $question->url);
    }

    /** @test */
    public function it_returns_correct_answer_styles()
    {
        $question = Question::factory()->create();
        $this->assertEquals('unanswered', $question->answer_styles);

        $question->answers()->create(['user_id' => User::factory()->create()->id, 'body' => 'test']);
        $this->assertEquals('answered', $question->fresh()->answer_styles);

        $question->markAsBest($question->answers->first());
        $this->assertEquals('has-best-answer', $question->fresh()->answer_styles);
    }

    /** @test */
    public function it_can_mark_best_answer()
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $question->markAsBest($answer);

        $this->assertEquals($answer->id, $question->fresh()->best_answer_id);
    }

    /** @test */
    public function it_can_be_voted()
    {
        $user = User::factory()->create();
        $question = Question::factory()->create();

        $question->vote(1, $user);

        $this->assertCount(1, $question->votes);
        $this->assertEquals(1, $question->votes_count);
    }

    /** @test */
    public function it_tracks_favorites()
    {
        $user = User::factory()->create();
        $question = Question::factory()->create();

        $question->favorites()->attach($user);

        $this->assertEquals(1, $question->favorites_count);
    }
}
