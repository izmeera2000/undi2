<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\MailController;
use App\Mail\FirstTimeLoginMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Member;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Support\Facades\Storage;

class MembersController extends Controller
{
    //

    public function index()
    {
        return view(view: 'members.profile');
    }


    public function getList(Request $request)
    {
        // Query only non-soft deleted members
        // $query = Member::query()->whereNull('deleted_at');  // Exclude soft-deleted members

        // Optionally, if using the SoftDeletes trait, you can also use:
        $query = Member::query()->withoutTrashed();  // This excludes soft-deleted members

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('members', function ($row) {
                return '
            <div class="d-flex align-items-center gap-3">
                <img src="' . $row->getProfilePictureUrlAttribute() . '" 
                     class="rounded-circle" width="40">
                <div>
                    <a href="' . route('members.show', $row->id) . '" class="fw-semibold">
                        ' . $row->nama . '
                    </a>
                    <div class="text-muted small">' . ($row->no_ahli) . '</div>
                </div>
            </div>';
            })

            ->addColumn('groups', function ($row) {

                if (!$row->relationLoaded('groups') || $row->groups->isEmpty()) {
                    return '<span class="text-muted">No Group</span>';
                }

                return $row->groups->map(function ($group) {
                    return '<span class="badge bg-info me-1">' . e($group->name) . '</span>';
                })->implode('');
            })

            ->addColumn('joined', function ($row) {
                return $row->created_at->format('d M Y');
            })

            ->addColumn('actions', function ($row) {
                return '
            <div class="btn-group">
                <a href="' . route('members.show', $row->id) . '" class="btn btn-sm btn-light" title="View">
                    <i class="bi bi-eye"></i>
                </a>
            </div>';
            })

            ->rawColumns(['members', 'groups', 'actions'])
            ->make(true);
    }


    public function show(Member $member)
    {
        return view('members.profile', compact('member'));
    }



    // EDIT
    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    // DELETE
    public function destroy(Member $member)
    {
        // Delete the member
        $member->delete();

        // Log the activity
        activity()
            ->causedBy(auth()->user()) // the user performing the action
            ->performedOn($member)     // the model being affected
            ->withProperties([
                'member_id' => $member->id
            ])
            ->log('Deleted a member');

        return response()->json(['success' => true]);
    }


    public function updateAvatar(Request $request, Member $member)
    {
        // Validate the uploaded file
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Ensure profile exists
        $profile = $member;

        // If the profile has a previous avatar, delete it from storage
        if ($profile->profile_picture && Storage::disk('public')->exists($profile->profile_picture)) {
            try {
                Storage::disk('public')->delete($profile->profile_picture);
            } catch (\Exception $e) {
                Log::error("Error deleting old profile picture: " . $e->getMessage());
            }
        }

        // Store the new avatar image
        $path = $request->file('avatar')->store('avatars', 'public');

        // Update the member profile with the new avatar path
        $profile->update([
            'profile_picture' => $path,
        ]);

        // Log the activity for updating the avatar
        activity()
            ->causedBy(auth()->user()) // the user performing the action
            ->performedOn($profile)    // the model being affected
            ->withProperties([
                'member_id' => $profile->id,
                'old_profile_picture' => $profile->getOriginal('profile_picture'), // old profile picture (before update)
                'new_profile_picture' => $path, // new profile picture path
            ])
            ->log('Updated profile avatar');

        // Return a success response with the new avatar URL
        return response()->json([
            'success' => true,
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

}
