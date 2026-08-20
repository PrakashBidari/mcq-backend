<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppPage;

class AppPageController extends Controller
{
    public function show(string $slug)
    {
        $page = AppPage::where('slug', $slug)->first();

        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'slug'                => $page->slug,
                'tagline'             => $page->tagline,
                'last_updated_label'  => $page->last_updated_label,
                'intro_text_1'        => $page->intro_text_1,
                'intro_text_2'        => $page->intro_text_2,
                'button_text'         => $page->button_text,
                'stat_1_value'        => $page->stat_1_value,
                'stat_2_value'        => $page->stat_2_value,
                'stat_3_value'        => $page->stat_3_value,
                'stat_4_value'        => $page->stat_4_value,
                'developer_name'      => $page->developer_name,
                'developer_role'      => $page->developer_role,
                'developer_url'       => $page->developer_url,
                'copyright_text'      => $page->copyright_text,
                'items'               => $page->items ?? [],
            ],
        ]);
    }
}
