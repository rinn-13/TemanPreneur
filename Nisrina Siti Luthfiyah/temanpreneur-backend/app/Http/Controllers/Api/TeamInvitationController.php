<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class TeamInvitationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get invitations sent by this user
        $sentInvitations = TeamInvitation::where('inviter_user_id', $user->id)
            ->with(['business', 'invited'])
            ->get();

        // Get invitations received by this user
        $receivedInvitations = TeamInvitation::where('invited_user_id', $user->id)
            ->with(['business', 'inviter'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sent' => $sentInvitations,
                'received' => $receivedInvitations
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'invited_user_id' => 'required|exists:users,id',
            'permissions' => 'nullable|array'
        ]);

        $user = $request->user();
        $business = Business::findOrFail($request->business_id);

        // Check if user owns this business
        if ($business->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengundang ke bisnis ini.'
            ], 403);
        }

        // Check if invited user is already a team member
        $existingMember = TeamMember::where('business_id', $business->id)
            ->where('user_id', $request->invited_user_id)
            ->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'message' => 'User sudah menjadi anggota tim.'
            ], 400);
        }

        // Check if invitation already exists
        $existingInvitation = TeamInvitation::where('business_id', $business->id)
            ->where('invited_user_id', $request->invited_user_id)
            ->where('status', 'pending')
            ->first();

        if ($existingInvitation) {
            return response()->json([
                'success' => false,
                'message' => 'Undangan sudah dikirim sebelumnya.'
            ], 400);
        }

        // Create invitation
        $invitation = TeamInvitation::create([
            'business_id' => $business->id,
            'inviter_user_id' => $user->id,
            'invited_user_id' => $request->invited_user_id,
            'invitation_code' => Str::random(8),
            'permissions' => $request->permissions ?? ['manage_products', 'manage_orders'],
            'expires_at' => now()->addDays(7)
        ]);

        // Send notification (you can implement notification later)
        // Notification::send($invitation->invited, new TeamInvitationNotification($invitation));

        return response()->json([
            'success' => true,
            'message' => 'Undangan berhasil dikirim.',
            'data' => $invitation->load(['business', 'invited'])
        ]);
    }

    public function accept(Request $request, $id)
    {
        $invitation = TeamInvitation::findOrFail($id);
        $user = $request->user();

        if ($invitation->invited_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menerima undangan ini.'
            ], 403);
        }

        if ($invitation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Undangan sudah tidak valid.'
            ], 400);
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return response()->json([
                'success' => false,
                'message' => 'Undangan sudah kadaluarsa.'
            ], 400);
        }

        // Update invitation status
        $invitation->update(['status' => 'accepted']);

        // Add user as team member
        TeamMember::create([
            'business_id' => $invitation->business_id,
            'user_id' => $user->id,
            'role' => 'staff',
            'permissions' => $invitation->permissions,
            'joined_at' => now()
        ]);

        // Update user role to seller if not already
        if ($user->role !== 'seller') {
            $user->update(['role' => 'seller']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Undangan berhasil diterima. Anda sekarang menjadi staff di bisnis ini.'
        ]);
    }

    public function decline(Request $request, $id)
    {
        $invitation = TeamInvitation::findOrFail($id);
        $user = $request->user();

        if ($invitation->invited_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menolak undangan ini.'
            ], 403);
        }

        if ($invitation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Undangan sudah tidak valid.'
            ], 400);
        }

        $invitation->update(['status' => 'declined']);

        return response()->json([
            'success' => true,
            'message' => 'Undangan berhasil ditolak.'
        ]);
    }

    public function show($id)
    {
        $invitation = TeamInvitation::with(['business', 'inviter', 'invited'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $invitation
        ]);
    }
}
