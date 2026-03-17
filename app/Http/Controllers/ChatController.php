<?php
// app/Http/Controllers/ChatController.php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $rooms = $user->rooms()->with('members')->get();
        $publicRooms = Room::where('is_private', false)->get();
        $allUsers = User::where('id', '!=', $user->id)->get();

        return view('chat.index', compact('user', 'rooms', 'publicRooms', 'allUsers'));
    }

    public function getMessages(Room $room)
    {
        $isMember = $room->members()->where('user_id', Auth::id())->exists();
        if (!$isMember) {
            return response()->json(['error' => 'Anda bukan anggota room ini'], 403);
        }

        $messages = $room->messages()
            ->with('user:id,username,display_name,avatar')
            ->orderBy('created_at', 'asc')
            ->take(100)
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Room $room)
    {
        try {
            $request->validate([
                'content' => 'required|string',
                'type' => 'nullable|in:text,file,image,system',
                'iv' => 'nullable|string',
                'file_name' => 'nullable|string',
                'file_path' => 'nullable|string',
                'file_size' => 'nullable|integer',
            ]);

            // Check if user is member
            $isMember = $room->members()->where('user_id', Auth::id())->exists();
            if (!$isMember) {
                // Auto-join if public room
                if (!$room->is_private) {
                    $room->members()->attach(Auth::id(), ['role' => 'member']);
                } else {
                    return response()->json(['error' => 'Anda bukan anggota room ini'], 403);
                }
            }

            $message = new Message();
            $message->room_id = $room->id;
            $message->user_id = Auth::id();
            $message->content = $request->input('content');
            $message->type = $request->input('type', 'text');
            $message->iv = $request->input('iv');
            $message->file_name = $request->input('file_name');
            $message->file_path = $request->input('file_path');
            $message->file_size = $request->input('file_size');
            $message->save();

            // Reload with user relation
            $message = Message::with('user:id,username,display_name,avatar')->find($message->id);

            return response()->json($message);

        } catch (\Exception $e) {
            Log::error('sendMessage error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
        }
    }

    public function createRoom(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50',
                'description' => 'nullable|string|max:255',
                'is_private' => 'nullable',
                'password' => 'nullable|string|min:4',
                'members' => 'nullable|array',
            ]);

            $isPrivate = filter_var($request->input('is_private', false), FILTER_VALIDATE_BOOLEAN);

            $room = new Room();
            $room->name = $request->input('name');
            $room->slug = Str::slug($request->input('name')) . '-' . Str::random(6);
            $room->description = $request->input('description');
            $room->is_private = $isPrivate;
            $room->password = $request->input('password') ? bcrypt($request->input('password')) : null;
            $room->created_by = Auth::id();
            $room->save();

            // Add creator as admin
            $room->members()->attach(Auth::id(), ['role' => 'admin']);

            // Add invited members
            if ($request->has('members') && is_array($request->input('members'))) {
                foreach ($request->input('members') as $memberId) {
                    if ($memberId != Auth::id()) {
                        $room->members()->attach($memberId, ['role' => 'member']);
                    }
                }
            }

            return response()->json($room->load('members'));

        } catch (\Exception $e) {
            Log::error('createRoom error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat room: ' . $e->getMessage()], 500);
        }
    }

    public function joinRoom(Request $request, Room $room)
    {
        if ($room->is_private && $room->password) {
            if (!password_verify($request->input('password', ''), $room->password)) {
                return response()->json(['error' => 'Password salah'], 403);
            }
        }

        $room->members()->syncWithoutDetaching([Auth::id() => ['role' => 'member']]);
        return response()->json(['status' => 'joined']);
    }

    public function leaveRoom(Room $room)
    {
        $room->members()->detach(Auth::id());
        return response()->json(['status' => 'left']);
    }

    public function getRoomMembers(Room $room)
    {
        $members = $room->members()
            ->select('users.id', 'username', 'display_name', 'avatar', 'is_online', 'public_key')
            ->get();
        return response()->json($members);
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:51200',
                'room_id' => 'required|exists:rooms,id',
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize(); // Get size BEFORE moving!
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('uploads'), $filename);

            return response()->json([
                'file_path' => '/uploads/' . $filename,
                'file_name' => $originalName,
                'file_size' => $fileSize,
            ]);

        } catch (\Exception $e) {
            Log::error('uploadFile error: ' . $e->getMessage());
            return response()->json(['error' => 'Upload gagal: ' . $e->getMessage()], 500);
        }
    }

    public function getOnlineUsers()
    {
        $users = User::where('is_online', true)
            ->where('id', '!=', Auth::id())
            ->select('id', 'username', 'display_name', 'avatar', 'public_key')
            ->get();
        return response()->json($users);
    }

    public function searchRooms(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $rooms = Room::where('name', 'ilike', "%{$query}%")
            ->orWhere('description', 'ilike', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json($rooms);
    }
}