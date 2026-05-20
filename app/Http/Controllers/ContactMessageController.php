<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactReplyMail;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->get();
        $unreadCount = ContactMessage::unread()->count();

        return view('contact-messages.index', compact('messages', 'unreadCount'));
    }

    public function show(string $id)
    {
        $message = ContactMessage::findOrFail($id);

        // Mark as read if unread
        if ($message->status === 'unread') {
            $message->markAsRead();
        }

        return view('contact-messages.show', compact('message'));
    }

    // public function reply(Request $request, string $id)
    // {
    //     $message = ContactMessage::findOrFail($id);

    //     $validated = $request->validate([
    //         'admin_reply' => 'required|string',
    //         'send_email' => 'boolean',
    //     ]);

    //     // Mark as replied
    //     $message->markAsReplied($validated['admin_reply']);

    //     // Send email if requested
    //     if ($request->has('send_email')) {
    //         try {
    //             // TODO: Create mail class and send email
    //             // Mail::to($message->email)->send(new ContactReplyMail($message));
    //         } catch (\Exception $e) {
    //             return redirect()->route('contact-messages.show', $id)
    //                 ->with('warning', 'Reply saved but email failed to send.');
    //         }
    //     }

    //     return redirect()->route('contact-messages.show', $id)
    //         ->with('success', 'Reply sent successfully!');
    // }

    public function destroy(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'ContactMessage')) {
            return redirect()->route('contact-messages.index')
                ->with('error', 'You do not have permission to delete messages.');
        }

        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->route('contact-messages.index')
            ->with('success', 'Message deleted successfully!');
    }

    public function markAsRead(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->markAsRead();

        return redirect()->back()->with('success', 'Message marked as read!');
    }

    public function markAsUnread(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['status' => 'unread', 'read_at' => null]);

        return redirect()->back()->with('success', 'Message marked as unread!');
    }

    public function reply(Request $request, string $id)
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'admin_reply' => 'required|string',
            'send_email' => 'boolean',
        ]);


        // dd($request->has('send_email'));

        // Mark as replied
        $message->markAsReplied($validated['admin_reply']);

        // Send email if requested
        if ($request->has('send_email')) {
            try {
                Mail::to($message->email)->send(new ContactReplyMail($message));

                return redirect()->route('contact-messages.show', $id)
                    ->with('success', 'Reply sent successfully and email notification has been delivered!');
            } catch (\Exception $e) {
                \Log::error('Email send failed: ' . $e->getMessage());

                return redirect()->route('contact-messages.show', $id)
                    ->with('warning', 'Reply saved but email failed to send. Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('contact-messages.show', $id)
            ->with('success', 'Reply saved successfully!');
    }
}
