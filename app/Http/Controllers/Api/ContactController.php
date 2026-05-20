<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    // Get contact settings
    public function getSettings()
    {
        $setting = ContactSetting::first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Contact settings not configured'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hero_title' => $setting->hero_title,
                'hero_subtitle' => $setting->hero_subtitle,
                'email' => $setting->email,
                'phone' => $setting->phone,
                'address' => $setting->address,
                'form_title' => $setting->form_title,
                'form_subtitle' => $setting->form_subtitle,
            ]
        ]);
    }

    // Submit contact form
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $contactMessage = ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message,
                'status' => 'unread',
            ]);

            // Optional: Send email notification to admin
            // You can implement this later
            // Mail::to(config('mail.admin_email'))->send(new NewContactMessage($contactMessage));

            return response()->json([
                'success' => true,
                'message' => 'Thank you for contacting us! We\'ll get back to you soon.',
                'data' => [
                    'id' => $contactMessage->id,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit message. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
