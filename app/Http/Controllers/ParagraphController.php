<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesQuestionImages;
use App\Models\Paragraph;
use App\Models\Question;
use App\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// A reading paragraph and its questions, written together in one form. In the app the
// paragraph and all of its questions are shown on one page; each question is still an
// ordinary question worth 1 mark. Uses the same permissions as Questions.
class ParagraphController extends Controller
{
    use ManagesQuestionImages;

    public function index()
    {
        if (!$this->can('read')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view paragraphs.');
        }

        $paragraphs = Paragraph::withCount('questions')
            ->with('questions.questionSets')
            ->latest()
            ->get();

        return view('paragraphs.index', compact('paragraphs'));
    }

    public function create()
    {
        if (!$this->can('create')) {
            return redirect()->route('paragraphs.index')->with('error', 'You do not have permission to create paragraphs.');
        }

        $questionSets = QuestionSet::with('category')->where('is_active', true)->get();

        return view('paragraphs.create', compact('questionSets'));
    }

    public function store(Request $request)
    {
        if (!$this->can('create')) {
            return redirect()->route('paragraphs.index')->with('error', 'You do not have permission to create paragraphs.');
        }

        $validated = $this->validateForm($request);

        DB::transaction(function () use ($request, $validated) {
            $paragraph = Paragraph::create([
                'title' => $validated['title'] ?? null,
                'content' => $validated['content'],
                'image' => $request->hasFile('image') ? $request->file('image')->store('paragraphs', 'public') : null,
            ]);

            $this->saveQuestions($request, $paragraph, $validated);
        });

        return redirect()->route('paragraphs.index')->with('success', 'Paragraph and questions created successfully!');
    }

    public function show(string $id)
    {
        return redirect()->route('paragraphs.edit', $id);
    }

    public function edit(string $id)
    {
        if (!$this->can('update')) {
            return redirect()->route('paragraphs.index')->with('error', 'You do not have permission to edit paragraphs.');
        }

        $paragraph = Paragraph::with(['questions.options', 'questions.questionSets'])->findOrFail($id);
        $questionSets = QuestionSet::with('category')->where('is_active', true)->get();

        return view('paragraphs.edit', compact('paragraph', 'questionSets'));
    }

    public function update(Request $request, string $id)
    {
        if (!$this->can('update')) {
            return redirect()->route('paragraphs.index')->with('error', 'You do not have permission to update paragraphs.');
        }

        $paragraph = Paragraph::with('questions.options')->findOrFail($id);
        $validated = $this->validateForm($request, $paragraph);

        DB::transaction(function () use ($request, $validated, $paragraph) {
            $data = [
                'title' => $validated['title'] ?? null,
                'content' => $validated['content'],
            ];

            if ($request->hasFile('image') || $request->boolean('remove_image')) {
                $this->deleteImage($paragraph->image);
                $data['image'] = $request->hasFile('image') ? $request->file('image')->store('paragraphs', 'public') : null;
            }

            $paragraph->update($data);

            $this->saveQuestions($request, $paragraph, $validated);
        });

        return redirect()->route('paragraphs.index')->with('success', 'Paragraph updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!$this->can('delete')) {
            return redirect()->route('paragraphs.index')->with('error', 'You do not have permission to delete paragraphs.');
        }

        $paragraph = Paragraph::with('questions.options')->findOrFail($id);

        // Its questions were written for this paragraph, so they go with it.
        foreach ($paragraph->questions as $question) {
            $this->deleteQuestion($question);
        }
        $this->deleteImage($paragraph->image);
        $paragraph->delete();

        return redirect()->route('paragraphs.index')->with('success', 'Paragraph and its questions deleted successfully!');
    }

    private function validateForm(Request $request, ?Paragraph $paragraph = null): array
    {
        $validated = $request->validate([
            'question_sets' => 'required|array|min:1',
            'question_sets.*' => 'exists:question_sets,id',
            'position' => 'nullable|integer|min:1|max:60',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'image' => self::IMAGE_RULE,
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|integer',
            'questions.*.question' => 'required|string',
            'questions.*.image' => self::IMAGE_RULE,
            'questions.*.options' => 'required|array|size:4',
            'questions.*.options.*' => 'nullable|string',
            'questions.*.option_images' => 'nullable|array',
            'questions.*.option_images.*' => self::IMAGE_RULE,
            'questions.*.correct_answer' => 'required|integer|min:0|max:3',
            'questions.*.difficulty' => 'required|in:Easy,Medium,Hard',
            'questions.*.explanation' => 'required|string',
        ], [
            'questions.required' => 'Add at least one question.',
            'questions.*.question.required' => 'The question text is required.',
            'questions.*.correct_answer.required' => 'Select the correct answer.',
            'questions.*.explanation.required' => 'The explanation is required.',
        ]);

        $errors = [];
        foreach (array_keys($validated['questions']) as $key) {
            $existing = $this->existingQuestion($paragraph, $validated['questions'][$key]['id'] ?? null);
            $errors += $this->optionErrors($request, "questions.$key.", $existing);
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    // Creates / updates the submitted questions in form order and deletes the
    // paragraph's questions that were removed from the form.
    private function saveQuestions(Request $request, Paragraph $paragraph, array $validated): void
    {
        $keptIds = [];

        foreach ($validated['questions'] as $key => $row) {
            $prefix = "questions.$key.";
            $question = $this->existingQuestion($paragraph, $row['id'] ?? null);

            $data = [
                'paragraph_id' => $paragraph->id,
                'question' => $row['question'],
                'explanation' => $row['explanation'],
                'difficulty' => $row['difficulty'],
                'correct_answer' => $row['correct_answer'],
                'position' => $validated['position'] ?? null,
                'image' => $this->questionImage($request, $prefix, $question),
            ];

            if ($question) {
                $question->update($data);
            } else {
                $question = Question::create($data);
            }

            $this->saveOptions($request, $question, $prefix);
            $question->questionSets()->sync($validated['question_sets']);
            $keptIds[] = $question->id;
        }

        foreach ($paragraph->questions()->whereNotIn('id', $keptIds)->get() as $removed) {
            $this->deleteQuestion($removed);
        }
    }

    // Only a question that really belongs to this paragraph can be updated through it.
    private function existingQuestion(?Paragraph $paragraph, $id): ?Question
    {
        if (!$paragraph || !$id) {
            return null;
        }

        return $paragraph->questions->firstWhere('id', (int) $id);
    }

    private function can(string $action): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->hasPermission($action, 'Question');
    }
}
