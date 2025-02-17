<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $guarded = [];
    /* Observer pattern
       boot: is hook (life cycle method)
    */
    public static function boot()
    {
        parent::boot();
        static::created(function(Answer $answer) {
            $answer->question->increment('answers_count');
        });
        static::deleted(function(Answer $answer) {
            $answer->question->decrement('answers_count');
        });
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function votes() {
        return $this->morphToMany(User::class, 'vote')->withTimestamps();
    }

    /* Accessors */
    public function getCreatedDateAttribute() {
        return $this->created_at->diffForHumans();
    }

    public function getBestAnswerStyleAttribute(Question $question) {
        return $this->id === $question->best_answer_id ? 'text-success' : 'text-dark';
    }

    public function vote(int $vote, User $user = null) {
        $user = $user ?? auth()->user();
        $this->votes()->attach($user->id, ['vote' => $vote]);
        if($vote < 0)
        {
            $this->decrement('votes_count');
        }
        else
        {
            $this->increment('votes_count');
        }
    }

    public function updateVote(int $vote, User $user = null) {
        $user = $user ?? auth()->user();
        $this->votes()->updateExistingPivot($user->id, ['vote' => $vote]);
        if($vote < 0)
        {
            $this->decrement('votes_count', 2);
        }
        else
        {
            $this->increment('votes_count', 2);
        }
    }
}
