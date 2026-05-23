<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register new user as BUYER only
     * POST /api/register
     */
    public function register(RegisterRequest $request)
    {
        // Validate access code
        $accessCode = null;
        $accessCodeInput = trim($request->input('access_code', ''));

        if (!empty($accessCodeInput)) {
            $accessCode = \App\Models\AccessCode::where('code', $accessCodeInput)
                ->where('is_used', false)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                })
                ->first();

            if (!$accessCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode akses tidak valid atau sudah kadaluarsa.',
                    'code' => 'invalid_access_code',
                ], 422);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'class' => $request->class,
            'role' => 'buyer',
            'roles' => ['buyer'],
        ]);

        if ($accessCode) {
            $accessCode->update(['is_used' => true, 'used_by' => $user->id, 'used_at' => now()]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil! Anda sekarang terdaftar sebagai pembeli.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     * POST /api/login
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan. Silakan daftar terlebih dahulu.',
                'code' => 'user_not_found',
            ], 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah.',
                'code' => 'invalid_password',
            ], 422);
        }

        if (($user->status ?? 'active') === 'banned') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda diblokir. Hubungi admin.',
                'code' => 'account_banned',
            ], 403);
        }

        // Load business relationship
        $user->load('business');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'user' => new UserResource($user),
            'business' => $user->business ? new \App\Http\Resources\BusinessResource($user->business) : null,
            'token' => $token,
        ], 200);
    }

    /**
     * Logout user
     * POST /api/logout
     */
   public function logout(Request $request)
{
    try {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Logout gagal'
        ], 500);
    }
}

    /**
     * Register as buyer (no longer needed - register already creates buyer)
     * @deprecated Use register() instead
     */
    public function registerAsBuyer(Request $request)
    {
        $user = $request->user();
        $user->addBuyerRole();
        $user->load('business');
        
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
            'message' => 'Akun berhasil didaftarkan sebagai pembeli.',
        ]);
    }

    /**
     * Get current authenticated user
     * GET /api/user
     */
    public function user(Request $request)
    {
        $user = $request->user()->load('business');
        
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
            'business' => $user->business ? new \App\Http\Resources\BusinessResource($user->business) : null,
        ], 200);
    }

    /**
     * Update user profile
     * PUT /api/user/profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = $request->user();
            
            $user->update($request->only(['name', 'phone', 'class']));
            $user->load('business');
            
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
                'user' => new UserResource($user),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     * POST /api/user/change-password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Kata sandi lama salah'], 422);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Kata sandi berhasil diubah']);
    }

    /**
     * Upload user profile photo
     * POST /api/user/upload-photo
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user = $request->user();
            
            // Delete old photo if exists
            if ($user->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
            }
            
            // Store new photo
            $path = $request->file('photo')->store('users/photos', 'public');
            $photoUrl = ImageUrl::normalize($path);
            
            $user->update(['photo' => $path]);
            $user->load('business');
            
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diubah.',
                'photo' => $photoUrl,
                'user' => new UserResource($user),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload foto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}