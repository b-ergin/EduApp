<?php

namespace App\Models;
use App\Models\Quiz;
use App\Models\Grade;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'grade_id'];
    public function quizzes()
    {
    return $this->hasMany(Quiz::class);
    }
    public function grade()
    {
    return $this->belongsTo(Grade::class);
    }
}
