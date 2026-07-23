<?php

namespace App\Helpers;

class PermissionHelper
{
    // Available models in the system
    public static function getAvailableModels()
    {
        return [
            'Category' => 'Categories',
            'QuestionSet' => 'Question Sets',
            'Question' => 'Questions',
            'Book' => 'Books',
            'Blog' => 'Blog',
            'Faq' => 'FAQ',
            'Advertise' => 'Advertise',
            'ContactMessage',
            'ContactSetting' => 'ContactSettings',
            'User' => 'Users',
        ];
    }

    // Available actions
    public static function getActions()
    {
        return [
            'create' => 'Create',
            'read' => 'View/Read',
            'update' => 'Edit/Update',
            'delete' => 'Delete',
        ];
    }

}
