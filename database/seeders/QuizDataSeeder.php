<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionSet;
use Illuminate\Support\Str;

class QuizDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories first
        $designCategory = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'description' => 'UI/UX and Design principles',
            'color' => '#7c3aed',
        ]);

        $developmentCategory = Category::create([
            'name' => 'Development',
            'slug' => 'development',
            'description' => 'Programming and Development',
            'color' => '#2563eb',
        ]);

        $businessCategory = Category::create([
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'Business and Management',
            'color' => '#059669',
        ]);

        $marketingCategory = Category::create([
            'name' => 'Marketing',
            'slug' => 'marketing',
            'description' => 'Digital Marketing',
            'color' => '#dc2626',
        ]);

        // Create Question Sets
        $designSet1 = QuestionSet::create([
            'category_id' => $designCategory->id,
            'name' => 'UI/UX Fundamentals',
            'description' => 'Basic UX design principles',
            'is_active' => true,
        ]);

        $designSet2 = QuestionSet::create([
            'category_id' => $designCategory->id,
            'name' => 'Advanced Design Patterns',
            'description' => 'Advanced design concepts',
            'is_active' => true,
        ]);

        $developmentSet1 = QuestionSet::create([
            'category_id' => $developmentCategory->id,
            'name' => 'JavaScript Basics',
            'description' => 'Fundamental JavaScript concepts',
            'is_active' => true,
        ]);

        $developmentSet2 = QuestionSet::create([
            'category_id' => $developmentCategory->id,
            'name' => 'React Advanced',
            'description' => 'Advanced React patterns',
            'is_active' => true,
        ]);

        // Sample questions data
        $questionsData = [
            // Design Set 1 Questions
            [
                'question' => 'What is the main principle of User-Centered Design?',
                'options' => [
                    'Making designs look beautiful',
                    'Focusing on users\' needs throughout the design process',
                    'Using the latest design trends',
                    'Creating complex interfaces'
                ],
                'correctAnswer' => 1,
                'difficulty' => 'Easy',
                'explanation' => 'User-Centered Design focuses on understanding and addressing users\' needs at every stage of the design process.',
                'questionSetId' => $designSet1->id,
            ],
            [
                'question' => 'What is the purpose of a persona in UX design?',
                'options' => [
                    'To represent the design team',
                    'To create fictional characters for testing',
                    'To represent typical users and their goals',
                    'To showcase design skills'
                ],
                'correctAnswer' => 2,
                'difficulty' => 'Medium',
                'explanation' => 'Personas are fictional characters created to represent different user types and help designers understand user needs and behaviors.',
                'questionSetId' => $designSet1->id,
            ],
            // Design Set 2 Questions
            [
                'question' => 'What is whitespace in design?',
                'options' => [
                    'Empty space in a design',
                    'White colored backgrounds',
                    'Margins only',
                    'Text spacing'
                ],
                'correctAnswer' => 0,
                'difficulty' => 'Easy',
                'explanation' => 'Whitespace (or negative space) is the empty space around design elements that helps create balance and focus.',
                'questionSetId' => $designSet2->id,
            ],
            // Development Set 1 Questions
            [
                'question' => 'Which programming language is known as the "language of the web"?',
                'options' => ['Python', 'Java', 'JavaScript', 'C++'],
                'correctAnswer' => 2,
                'difficulty' => 'Easy',
                'explanation' => 'JavaScript is the primary language for web development, running in browsers to create interactive web pages.',
                'questionSetId' => $developmentSet1->id,
            ],
        ];

        foreach ($questionsData as $questionData) {
            // Create question
            $question = Question::create([
                'question' => $questionData['question'],
                'explanation' => $questionData['explanation'],
                'difficulty' => $questionData['difficulty'],
                'correct_answer' => $questionData['correctAnswer'],
            ]);

            // Create options
            foreach ($questionData['options'] as $index => $optionText) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'option_index' => $index,
                ]);
            }

            // Attach to question set
            $question->questionSets()->attach($questionData['questionSetId'], ['order' => $question->id]);
        }

        $this->command->info('Quiz data seeded successfully!');
        $this->command->info('Categories: ' . Category::count());
        $this->command->info('Question Sets: ' . QuestionSet::count());
        $this->command->info('Questions: ' . Question::count());
    }
}
