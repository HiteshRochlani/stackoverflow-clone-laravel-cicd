<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function store(Request $request, Question $question)
    {
        $question->favorites()->attach([auth()->id()]);
        return redirect()->back();
    }

    public function destroy(Request $request, Question $question)
    {
        $question->favorites()->detach([auth()->id()]);
        return redirect()->back();
    }
}
