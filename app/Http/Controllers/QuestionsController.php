<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;

class QuestionsController extends Controller
{
    public function __construct() {
        $this->middleware(['auth'])->only(['create', 'store']);
    }
    public function index()
    {
        $questions = Question::with('owner')->latest()->paginate(10);
        return view('questions.index', compact(['questions']));
    }

    public function create()
    {
        return view('questions.create');
    }

    public function store(CreateQuestionRequest $request)
    {
        auth()->user()->questions()->create([
            'title'=>$request->title,
            'body'=>$request->body
        ]);

        session()->flash('success', 'Question has been added successfully!');
        return redirect(route('questions.index'));
    }

    public function edit(Question $question)
    {
        if($this->authorize('update', $question)) {
            return view('questions.edit', compact([
                'question'
            ]));
        }
        abort(403);
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        if($this->authorize('update', $question)) {
            $question->update([
                'title'=>$request->title,
                'body'=>$request->body
            ]);

            session()->flash('success', 'Question has been updated successfully!');
            return redirect(route('questions.index'));
        }
        abort(403);
    }

    public function destroy(Question $question)
    {
        if($this->authorize('delete', $question)) {
            $question->delete();

            session()->flash('success', 'Question has been deleted successfully!');
            return redirect(route('questions.index'));
        }
        abort(403);
    }

    public function show(Question $question) {
        $question->increment('views_count');

        return view('questions.show', compact([
            'question'
        ]));
    }
}
