<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use App\Http\Controllers\Concerns\ManagesQuestionImages;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    use ManagesQuestionImages;

    public function index(Request $request)
    {
        $query = Question::with(['options', 'questionSets.category.parent', 'paragraph']);

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

        $validated = $request->validate($this->rules());
        if ($errors = $this->optionErrors($request)) {
            throw ValidationException::withMessages($errors);
        }

        $question = Question::create([
            'question' => $validated['question'],
            'explanation' => $validated['explanation'],
            'difficulty' => $validated['difficulty'],
            'correct_answer' => $validated['correct_answer'],
            'position' => $validated['position'],
            'image' => $this->questionImage($request),
        ]);

        $this->saveOptions($request, $question);

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

        // Paragraph questions are written and edited inside their paragraph.
        if ($question->paragraph_id) {
            return redirect()->route('paragraphs.edit', $question->paragraph_id);
        }

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

        $question->load('options');

        $validated = $request->validate($this->rules());
        if ($errors = $this->optionErrors($request, '', $question)) {
            throw ValidationException::withMessages($errors);
        }

        $question->update([
            'question' => $validated['question'],
            'explanation' => $validated['explanation'],
            'difficulty' => $validated['difficulty'],
            'correct_answer' => $validated['correct_answer'],
            'position' => $validated['position'],
            'image' => $this->questionImage($request, '', $question),
        ]);

        $this->saveOptions($request, $question);

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
        $this->deleteQuestion($question);

        return redirect()->route('questions.index')->with('success', 'Question deleted successfully!');
    }

    private function rules(): array
    {
        return [
            'question_sets' => 'required|array|min:1',
            'question_sets.*' => 'exists:question_sets,id',
            'question' => 'required|string',
            'image' => self::IMAGE_RULE,
            'options' => 'required|array|size:4',
            'options.*' => 'nullable|string',
            'option_images' => 'nullable|array',
            'option_images.*' => self::IMAGE_RULE,
            'correct_answer' => 'required|integer|min:0|max:3',
            'difficulty' => 'required|in:Easy,Medium,Hard',
            'explanation' => 'required|string',
            'position' => 'nullable|integer|min:1|max:60',
        ];
    }
}
