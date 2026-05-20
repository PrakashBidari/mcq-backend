<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\QuestionOption;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::with(['options', 'questionSets']);

        // Filter by category (through question sets)
        if ($request->filled('category')) {
            $query->whereHas('questionSets', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter by question set
        if ($request->filled('question_set')) {
            $query->whereHas('questionSets', function ($q) use ($request) {
                $q->where('question_set_id', $request->question_set);
            });
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $questions = $query->get();

        $categories = Category::all();
        $questionSets = QuestionSet::with('category')->get();

        return view('questions.index', compact('questions', 'categories', 'questionSets'));
    }

    public function create()
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Question')) {
            return redirect()->route('questions.index')->with('error', 'You do not have permission to create questions.');
        }

        $questionSets = QuestionSet::with('category')->where('is_active', true)->get();
        return view('questions.create', compact('questionSets'));
    }

    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Question')) {
            return redirect()->route('questions.index')->with('error', 'You do not have permission to create questions.');
        }

        $validated = $request->validate([
            'question_sets' => 'required|array|min:1',
            'question_sets.*' => 'exists:question_sets,id',
            'question' => 'required|string',
            'options' => 'required|array|size:4',
            'options.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0|max:3',
            'difficulty' => 'required|in:Easy,Medium,Hard',
            'explanation' => 'required|string',
            'position' => 'nullable|integer|min:1|max:60',
        ]);

        // Create question
        $question = Question::create([
            'question' => $validated['question'],
            'explanation' => $validated['explanation'],
            'difficulty' => $validated['difficulty'],
            'correct_answer' => $validated['correct_answer'],
            'position' => $validated['position'],
        ]);

        // Create options
        foreach ($validated['options'] as $index => $optionText) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $optionText,
                'option_index' => $index,
            ]);
        }

        // Attach to question sets
        $question->questionSets()->attach($validated['question_sets']);

        return redirect()->route('questions.index')->with('success', 'Question created successfully!');
    }

    public function edit(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Question')) {
            return redirect()->route('questions.index')->with('error', 'You do not have permission to edit questions.');
        }

        $question = Question::with(['options', 'questionSets'])->findOrFail($id);
        $questionSets = QuestionSet::with('category')->where('is_active', true)->get();

        return view('questions.edit', compact('question', 'questionSets'));
    }

    public function update(Request $request, string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Question')) {
            return redirect()->route('questions.index')->with('error', 'You do not have permission to update questions.');
        }

        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'question_sets' => 'required|array|min:1',
            'question_sets.*' => 'exists:question_sets,id',
            'question' => 'required|string',
            'options' => 'required|array|size:4',
            'options.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0|max:3',
            'difficulty' => 'required|in:Easy,Medium,Hard',
            'explanation' => 'required|string',
            'position' => 'nullable|integer|min:1|max:60',
        ]);

        // Update question
        $question->update([
            'question' => $validated['question'],
            'explanation' => $validated['explanation'],
            'difficulty' => $validated['difficulty'],
            'correct_answer' => $validated['correct_answer'],
            'position' => $validated['position'],
        ]);

        // Update options
        foreach ($validated['options'] as $index => $optionText) {
            $question->options()->where('option_index', $index)->update([
                'option_text' => $optionText,
            ]);
        }

        // Sync question sets
        $question->questionSets()->sync($validated['question_sets']);

        return redirect()->route('questions.index')->with('success', 'Question updated successfully!');
    }

    public function destroy(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'Question')) {
            return redirect()->route('questions.index')->with('error', 'You do not have permission to delete questions.');
        }

        $question = Question::findOrFail($id);
        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Question deleted successfully!');
    }


}
