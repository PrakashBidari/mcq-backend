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
            'BookCategory' => 'Book Categories',
            'Blog' => 'Blog',
            'BlogCategory' => 'Blog Categories',
            'Faq' => 'FAQ',
            'Advertise' => 'Advertise',
            'Package' => 'Question Set Packages',
            'PriceTier' => 'Price Tiers',
            'AttemptPack' => 'Attempt Packs',
            'SubscriptionPlan' => 'Subscription Plans',
            'Purchase' => 'Purchases',
            'ContactMessage',
            'ContactSetting' => 'ContactSettings',
            'User' => 'Users',
            'Banner' => 'Banners',
            'AppContent' => 'App Content',
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
