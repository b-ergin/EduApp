<?php

namespace App\Models;
use App\Models\Subject;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['name'];
    public function subjects()
    {
    return $this->hasMany(Subject::class);
    }
}
