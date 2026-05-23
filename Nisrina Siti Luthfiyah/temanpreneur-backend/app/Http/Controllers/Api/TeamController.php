<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index(Business $business)
    {
        try {
            $this->authorize('viewAny', TeamMember::class);

            $team = $business->teamMembers()->with('user')->get();

            return response()->json($team, 200);
        } catch (\Exception $e) {
            Log::error('Team index error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch team'], 500);
        }
    }

    public function store(Request $request, Business $business)
    {
        try {
            $this->authorize('create', TeamMember::class);

            $request->validate([
                'user_email' => 'required|email|exists:users,email',
                'role' => 'required|string|in:admin,editor,moderator',
            ]);

            $user = User::where('email', $request->user_email)->first();
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Prevent adding business owner as team member
            if ($user->id === $business->user_id) {
                return response()->json(['message' => 'Business owner cannot be added as team member'], 400);
            }

            // Check if already member
            if ($business->teamMembers()->where('user_id', $user->id)->exists()) {
                return response()->json(['message' => 'User already in team'], 400);
            }

            TeamMember::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'role' => $request->role,
            ]);

            return response()->json(['message' => 'Team member added'], 201);
        } catch (\Exception $e) {
            Log::error('Team store error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add team member'], 500);
        }
    }

    public function show(TeamMember $teamMember)
    {
        try {
            $this->authorize('view', $teamMember);

            return response()->json($teamMember->load('user', 'business'), 200);
        } catch (\Exception $e) {
            Log::error('Team show error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to show team member'], 500);
        }
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        try {
            $this->authorize('update', $teamMember);

            $request->validate([
                'role' => 'required|string|in:admin,editor,moderator',
            ]);

            $teamMember->update($request->only('role'));

            return response()->json($teamMember->fresh()->load('user'), 200);
        } catch (\Exception $e) {
            Log::error('Team update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update team member'], 500);
        }
    }

    public function destroy(TeamMember $teamMember)
    {
        try {
            $this->authorize('delete', $teamMember);

            $teamMember->delete();

            return response()->json(['message' => 'Team member removed'], 200);
        } catch (\Exception $e) {
            Log::error('Team destroy error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove team member'], 500);
        }
    }
}

