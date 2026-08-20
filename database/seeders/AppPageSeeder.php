<?php

namespace Database\Seeders;

use App\Models\AppPage;
use Illuminate\Database\Seeder;

class AppPageSeeder extends Seeder
{
    public function run(): void
    {
        AppPage::firstOrCreate(
            ['slug' => 'about-app'],
            [
                'tagline'        => 'Learn · Practice · Succeed',
                'intro_text_1'   => 'MCQ Hub is a modern learning platform designed to help students, professionals, and lifelong learners master any subject through smart practice, curated content, and progress tracking.',
                'intro_text_2'   => "Whether you're preparing for exams, upgrading your skills, or exploring new topics — MCQ Hub gives you the tools to learn faster and retain more.",
                'stat_1_value'   => '10K+',
                'stat_2_value'   => '50+',
                'stat_3_value'   => '5K+',
                'stat_4_value'   => '4.8★',
                'developer_name' => 'Ikigai Job Placement',
                'developer_role' => 'Product Team',
                'developer_url'  => 'https://app.ikigaijobplacement.com',
                'copyright_text' => '© 2025 Ikigai Job Placement. All rights reserved.',
                'items' => [
                    [
                        'title'   => 'Smart MCQ Practice',
                        'content' => 'Practice thousands of multiple choice questions across all major subjects and categories.',
                        'icon'    => 'school',
                    ],
                    [
                        'title'   => 'Rich Study Library',
                        'content' => 'Access a curated collection of books and study materials to deepen your knowledge.',
                        'icon'    => 'book',
                    ],
                    [
                        'title'   => 'Track Progress',
                        'content' => 'Monitor your performance, streaks, and achievements as you improve every day.',
                        'icon'    => 'trending-up',
                    ],
                    [
                        'title'   => 'Secure & Private',
                        'content' => 'Your data is protected. We never sell your personal information to third parties.',
                        'icon'    => 'star',
                    ],
                ],
            ],
        );

        AppPage::firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'last_updated_label' => 'Last updated: January 2025',
                'intro_text_1'       => 'This policy explains how MCQ Hub collects, uses, and protects your personal information. Tap any section to expand it.',
                'items' => [
                    [
                        'title'   => 'Information We Collect',
                        'content' => "We collect information you provide directly to us when you create an account, such as your name, email address, and profile details.\n\nWe also automatically collect certain information when you use our app, including:\n\n• Device information (model, OS version)\n• Usage data (screens visited, quizzes taken)\n• Performance and crash data to improve stability",
                        'icon'    => 'person',
                    ],
                    [
                        'title'   => 'How We Use Your Information',
                        'content' => "We use the information we collect to:\n\n• Provide, maintain, and improve our services\n• Personalize your learning experience\n• Track your progress and achievements\n• Send you updates and notifications (with your consent)\n• Respond to your comments and questions\n• Monitor and analyze usage patterns to enhance the app",
                        'icon'    => 'analytics',
                    ],
                    [
                        'title'   => 'Information Sharing',
                        'content' => "We do not sell, trade, or rent your personal information to third parties.\n\nWe may share your information only in these limited circumstances:\n\n• With service providers who assist us in operating our app\n• When required by law or to protect our legal rights\n• In connection with a merger or acquisition (you will be notified)\n\nAll third-party partners are bound by strict data protection agreements.",
                        'icon'    => 'people',
                    ],
                    [
                        'title'   => 'Data Security',
                        'content' => "We take the security of your data seriously. We implement industry-standard security measures including:\n\n• SSL/TLS encryption for all data transmission\n• Secure token-based authentication\n• Regular security audits and vulnerability testing\n• Encrypted storage of sensitive information\n\nHowever, no method of transmission over the internet is 100% secure. We encourage you to use a strong, unique password.",
                        'icon'    => 'briefcase',
                    ],
                    [
                        'title'   => 'Data Retention',
                        'content' => "We retain your personal information for as long as your account is active or as needed to provide our services.\n\nYou may request deletion of your account and associated data at any time by contacting us. We will process your request within 30 days.\n\nSome information may be retained for legal or legitimate business purposes even after account deletion.",
                        'icon'    => 'library',
                    ],
                    [
                        'title'   => 'Your Rights',
                        'content' => "You have the following rights regarding your personal data:\n\n• Access: Request a copy of the data we hold about you\n• Correction: Update or correct inaccurate information\n• Deletion: Request removal of your personal data\n• Portability: Receive your data in a machine-readable format\n• Objection: Opt out of certain data processing activities\n\nTo exercise any of these rights, please contact us through the app.",
                        'icon'    => 'flag',
                    ],
                    [
                        'title'   => 'Changes to This Policy',
                        'content' => "We may update this Privacy Policy from time to time to reflect changes in our practices or for legal reasons.\n\nWhen we make significant changes, we will notify you through the app or by email. The \"Last Updated\" date at the bottom of this page indicates when the policy was last revised.\n\nContinued use of the app after changes constitutes acceptance of the updated policy.",
                        'icon'    => 'newspaper',
                    ],
                ],
            ],
        );

        AppPage::firstOrCreate(
            ['slug' => 'study-library'],
            [
                'tagline'      => '📖 STUDY LIBRARY',
                'intro_text_1' => "Explore 100+\nFree Books",
                'intro_text_2' => 'Design, Dev, Business & more',
                'button_text'  => 'Browse',
            ],
        );
    }
}
