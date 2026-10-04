<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Question / answer-option image handling shared by normal questions and the
// questions written inside a paragraph. $prefix is the request key the question's
// fields live under: '' for a normal question, 'questions.<key>.' for a paragraph one.
trait ManagesQuestionImages
{
    protected const IMAGE_RULE = 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:4096';

    // Each option needs text, an image, or both. An existing question may keep the
    // option image it already has, as long as it isn't being removed.
    protected function optionErrors(Request $request, string $prefix = '', ?Question $question = null): array
    {
        $errors = [];

        foreach (range(0, 3) as $index) {
            $hasText = trim((string) $request->input("{$prefix}options.$index")) !== '';
            $hasNewImage = $request->hasFile("{$prefix}option_images.$index");
            $keepsImage = $question
                && $question->options->firstWhere('option_index', $index)?->option_image
                && !$request->boolean("{$prefix}remove_option_images.$index");

            if (!$hasText && !$hasNewImage && !$keepsImage) {
                $errors["{$prefix}options.$index"] = 'Option ' . chr(65 + $index) . ' needs text or an image.';
            }
        }

        return $errors;
    }

    // New question image path, or the current one (possibly removed / replaced).
    protected function questionImage(Request $request, string $prefix = '', ?Question $question = null): ?string
    {
        $current = $question?->image;

        if ($request->hasFile("{$prefix}image") || $request->boolean("{$prefix}remove_image")) {
            $this->deleteImage($current);
            return $request->hasFile("{$prefix}image")
                ? $request->file("{$prefix}image")->store('questions', 'public')
                : null;
        }

        return $current;
    }

    // Creates or updates the 4 options (text + optional image) of a question.
    protected function saveOptions(Request $request, Question $question, string $prefix = ''): void
    {
        $question->loadMissing('options');

        foreach (range(0, 3) as $index) {
            $option = $question->options->firstWhere('option_index', $index)
                ?? new QuestionOption(['question_id' => $question->id, 'option_index' => $index]);

            $option->option_text = $request->input("{$prefix}options.$index");

            if ($request->hasFile("{$prefix}option_images.$index") || $request->boolean("{$prefix}remove_option_images.$index")) {
                $this->deleteImage($option->option_image);
                $option->option_image = $request->hasFile("{$prefix}option_images.$index")
                    ? $request->file("{$prefix}option_images.$index")->store('questions/options', 'public')
                    : null;
            }

            $option->save();
        }
    }

    // Deletes a question together with its question / option images.
    protected function deleteQuestion(Question $question): void
    {
        $question->loadMissing('options');
        $this->deleteImage($question->image);
        foreach ($question->options as $option) {
            $this->deleteImage($option->option_image);
        }
        $question->delete();
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
