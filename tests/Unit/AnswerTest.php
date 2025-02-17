<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_increments_question_answers_count_on_creation()
    {
        $question = Question::factory()->create();

        Answer::factory()->create(['question_id' => $question->id]);

        $this->assertEquals(1, $question->fresh()->answers_count);
    }

    /** @test */
    public function it_decrements_question_answers_count_on_deletion()
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $answer->delete();

        $this->assertEquals(0, $question->fresh()->answers_count);
    }

    /** @test */
    public function it_belongs_to_a_question()
    {
        $answer = Answer::factory()->create();

        $this->assertInstanceOf(Question::class, $answer->question);
    }

    /** @test */
    public function it_belongs_to_an_author()
    {
        $answer = Answer::factory()->create();

        $this->assertInstanceOf(User::class, $answer->author);
    }

    /** @test */
    public function it_can_be_voted()
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();

        $answer->vote(1, $user);

        $this->assertCount(1, $answer->votes);
        $this->assertEquals(1, $answer->votes_count);
    }

    /** @test */
    public function it_can_update_vote()
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();

        $answer->vote(1, $user);
        $answer->updateVote(-1, $user);

        $this->assertEquals(-1, $answer->votes_count);
    }

    /** @test */
    public function it_has_created_date_accessor()
    {
        $answer = Answer::factory()->create();

        $this->assertEquals($answer->created_at->diffForHumans(), $answer->created_date);
    }

    /** @test */
    public function it_returns_correct_best_answer_style()
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $question->markAsBest($answer);

        $this->assertEquals('text-success', $answer->getBestAnswerStyleAttribute($question));
        $this->assertEquals('text-dark', $answer->getBestAnswerStyleAttribute(Question::factory()->create()));
    }
}
