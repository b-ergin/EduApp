<?php

namespace App\Models;
use App\Models\Question;
use App\Models\Subject;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['title', 'subject_id'];
    public function questions()
    {
    return $this->hasMany(Question::class);
    }
    public function subject()
    {
    return $this->belongsTo(Subject::class);
    }
}
