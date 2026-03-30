<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Choice;
use App\Models\Quiz;

class Question extends Model
{
    protected $fillable = ['question_text', 'quiz_id', 'image_path'];
    public function choices()
    {
    return $this->hasMany(Choice::class);
    }
    public function quiz()
    {
    return $this->belongsTo(Quiz::class);
    }
}
