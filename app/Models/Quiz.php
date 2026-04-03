<?php

namespace App\Models;

use App\Models\Question;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'subject_id',
        'sort_order',
        'is_challenge',
        'challenge_window_size',
        'challenge_min_stars',
        'xp_weight',
    ];

    protected $casts = [
        'is_challenge' => 'boolean',
        'xp_weight' => 'integer',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
