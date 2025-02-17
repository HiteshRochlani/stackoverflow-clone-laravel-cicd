<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAnswerRequest;
use App\Http\Requests\MarkAsBestRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Models\Answer;
use App\Models\Question;
use App\Notifications\NewReplyAdded;
use Illuminate\Http\Request;

class AnswersController extends Controller
{
    public function store(CreateAnswerRequest $request, Question $question)
    {
        $question->answers()->create([
            'body' => $request->body,
            'user_id' => auth()->id()
        ]);

        $question->owner->notify(new NewReplyAdded($question));
        session()->flash('success', 'Answer added successfully!');
        return redirect($question->url);
    }

    public function edit(Question $question, Answer $answer)
    {
        $this->authorize('update', $answer);
        return view('answers.edit', compact([
            'question',
            'answer'
        ]));
    }

    public function update(UpdateAnswerRequest $request, Question $question, Answer $answer)
    {
        $this->authorize('update', $answer);
        $answer->update([
            'body' => $request->body
        ]);

        session()->flash('success', 'Answer updated successfully!');
        return redirect($question->url);
    }

    public function markAsBest(MarkAsBestRequest $request, Question $question, Answer $answer)
    {
        $this->authorize('markAsBest', $question);
        if($answer->question_id !== $question->id) {
            abort(403);
        }
        $question->markAsBest($answer);
        return redirect()->back();
    }

    public function destroy(Question $question, Answer $answer)
    {
        $this->authorize('delete', [$answer, $question]);
        $answer->delete();

        session()->flash('success', 'Answer deleted successfully!');
        return redirect()->back();
    }
}
