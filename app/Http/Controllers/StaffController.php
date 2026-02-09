<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\MailController;
use App\Mail\FirstTimeLoginMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    // Page
    public function index()
    {
        return view('staff.profile');
    }

    // Server-side DataTable
    public function getStaff(Request $request)
    {
        $query = User::query();

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('staff', function ($row) {
                return '
                <div class="d-flex align-items-center gap-3">
                    <img src="' . asset('assets/img/profile-img.webp') . '" 
                         class="rounded-circle" width="40">
                    <div>
                        <a href="' . route('staff.show', $row->id) . '" class="fw-semibold">
                            ' . e($row->name) . '
                        </a>
                        <div class="text-muted small">' . e($row->email) . '</div>
                    </div>
                </div>';
            })

            ->addColumn('role', function ($row) {
                return '<span class="badge bg-info">' . ucfirst($row->role) . '</span>';
            })

            ->addColumn('joined', function ($row) {
                return $row->created_at->format('d M Y');
            })

            ->addColumn('actions', function ($row) {
                return '
                <div class="btn-group">
                    <div class="btn-group">
                        <a href="' . route('staff.show', $row->id) . '" class="btn btn-sm btn-light" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
 
                       
                    </div>
                </div>';
            })

            ->rawColumns(['staff', 'role', 'actions'])
            ->make(true);
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
        ]);

        // generate a temporary random password
        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'role' => $request->role,
        ]);
        $user->profile()->create();
        // attach plain password for email
        $user->password_plain = $temporaryPassword;

        Mail::to($user->email)->send(new FirstTimeLoginMail($user));


        return response()->json(['success' => true]);
    }

    // SHOW
    public function show(User $staff)
    {
        return view('staff.profile', compact('staff'));
    }

    // EDIT
    public function edit(User $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    // DELETE
    public function destroy(User $staff)
    {
        $staff->delete();
        return response()->json(['success' => true]);
    }


    public function suspend(User $user)
    {
        $user->update(['status' => 'suspended']);

        // Remove all sessions
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return response()->json(['success' => true]);
    }

    public function activate(User $user)
    {
        $user->update(['status' => 'active']);

        return response()->json(['success' => true]);
    }

    public function changeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,moderator,user',
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateProfile(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $user->update([
            'name' => $request->name,
        ]);

        $user->profile()->updateOrCreate([], [
            'bio' => $request->bio,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Return updated user + profile
        return response()->json([
            'success' => true,
            'user' => $user->load('profile'),
        ]);
    }



    public function changePassword(Request $request, User $user)
    {
        // Only admin OR the owner can change password
        if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            abort(403);
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'status' => 'active', // optional: mark active after first password set
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }


    public function updateAvatar(Request $request, User $user)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Ensure profile exists
        $profile = $user->profile()->firstOrCreate([]);

        // Delete old image
        if ($profile->profile_picture && Storage::disk('public')->exists($profile->profile_picture)) {
            Storage::disk('public')->delete($profile->profile_picture);
        }

        // Store new image
        $path = $request->file('avatar')->store('avatars', 'public');

        // Update profile table
        $profile->update([
            'profile_picture' => $path,
        ]);

        return response()->json([
            'success' => true,
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

}
