<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['admin', 'teacher'])
            ->where('id', '!=', auth()->id())
            ->get(['id', 'name', 'role', 'profile_image']);

        return view('chat.index', compact('users'));
    }

    public function messages(User $user)
    {
        $me = auth()->id();

        $messages = ChatMessage::with('sender:id,name')
            ->where(function ($q) use ($me, $user) {
                $q->where('sender_id', $me)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($me, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $me);
            })
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'sender_id'       => $m->sender_id,
                'body'            => $m->body,
                'attachment'      => $m->attachment ? Storage::url($m->attachment) : null,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'time'            => $m->created_at->format('g:i A'),
                'mine'            => (int) $m->sender_id === (int) $me,
                'deleted'         => !is_null($m->deleted_at),
            ]);

        ChatMessage::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'nullable|string|max:2000',
            'attachment'  => 'nullable|file|max:20480', // 20MB
        ]);

        abort_if(!$request->body && !$request->hasFile('attachment'), 422);

        $receiver = User::findOrFail($request->receiver_id);
        abort_if(!in_array($receiver->role, ['admin', 'teacher']), 403);

        $data = [
            'sender_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body ?? '',
        ];

        if ($request->hasFile('attachment')) {
            $file    = $request->file('attachment');
            $path    = $file->store('chat-attachments', 'public');
            $isImage = str_starts_with($file->getMimeType(), 'image/');

            $data['attachment']      = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_type'] = $isImage ? 'image' : 'file';
        }

        $message = ChatMessage::create($data);
        $message->load('sender:id,name');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'id'              => $message->id,
            'sender_id'       => $message->sender_id,
            'body'            => $message->body,
            'attachment'      => $message->attachment ? Storage::url($message->attachment) : null,
            'attachment_name' => $message->attachment_name,
            'attachment_type' => $message->attachment_type,
            'time'            => $message->created_at->format('g:i A'),
            'mine'            => true,
        ]);
    }

    public function media(User $user)
    {
        $me = auth()->id();

        $items = ChatMessage::where(function ($q) use ($me, $user) {
                $q->where(function ($q) use ($me, $user) {
                    $q->where('sender_id', $me)->where('receiver_id', $user->id);
                })->orWhere(function ($q) use ($me, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $me);
                });
            })
            ->whereNotNull('attachment')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => [
                'id'   => $m->id,
                'url'  => Storage::url($m->attachment),
                'name' => $m->attachment_name,
                'type' => $m->attachment_type,
                'time' => $m->created_at->format('M d, Y'),
                'mine' => (int) $m->sender_id === (int) $me,
            ]);

        return response()->json($items);
    }

    public function destroy(ChatMessage $message)
    {
        abort_if((int) $message->sender_id !== (int) auth()->id(), 403);

        if ($message->attachment) {
            Storage::disk('public')->delete($message->attachment);
        }

        $message->update(['deleted_at' => now()]);

        broadcast(new MessageDeleted($message));

        return response()->json(['success' => true]);
    }

    public function unread()
    {
        $counts = ChatMessage::where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->groupBy('sender_id')
            ->selectRaw('sender_id, count(*) as count')
            ->pluck('count', 'sender_id');

        return response()->json($counts);
    }
}
