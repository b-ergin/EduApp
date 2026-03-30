<?php

namespace Tests\Feature;

use App\Models\Choice;
use App\Models\Grade;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_quiz(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grade = Grade::create(['name' => 'Grade 5']);
        $subject = Subject::create([
            'name' => 'Math',
            'grade_id' => $grade->id,
        ]);

        $response = $this->actingAs($admin)->post('/admin/quizzes', [
            'title' => 'Fractions Quiz',
            'subject_id' => $subject->id,
        ]);

        $response->assertRedirect('/admin/quizzes');
        $this->assertDatabaseHas('quizzes', ['title' => 'Fractions Quiz']);
    }

    public function test_admin_choice_update_unsets_other_correct_choices_for_same_question(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grade = Grade::create(['name' => 'Grade 3']);
        $subject = Subject::create(['name' => 'Science', 'grade_id' => $grade->id]);
        $quiz = $subject->quizzes()->create(['title' => 'Plants']);
        $question = Question::create([
            'question_text' => 'Which part absorbs water?',
            'quiz_id' => $quiz->id,
        ]);

        $first = Choice::create([
            'choice_text' => 'Root',
            'question_id' => $question->id,
            'is_correct' => true,
        ]);

        $second = Choice::create([
            'choice_text' => 'Leaf',
            'question_id' => $question->id,
            'is_correct' => false,
        ]);

        $response = $this->actingAs($admin)->put('/admin/choices/'.$second->id, [
            'choice_text' => 'Leaf',
            'question_id' => $question->id,
            'is_correct' => 1,
        ]);

        $response->assertRedirect('/admin/choices');

        $this->assertDatabaseHas('choices', [
            'id' => $second->id,
            'is_correct' => true,
        ]);

        $this->assertDatabaseHas('choices', [
            'id' => $first->id,
            'is_correct' => false,
        ]);
    }
}
