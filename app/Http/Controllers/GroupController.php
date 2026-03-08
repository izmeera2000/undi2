<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\Member;
use App\Models\MemberGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class GroupController extends Controller
{
    /**
     * List all groups with members (DataTables AJAX)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $groups = Group::with(['members', 'creator']);

            return DataTables::of($groups)
                ->addIndexColumn()
                ->addColumn('members', function ($group) {
                    // Render member list with remove buttons
    


                    $html = '<div class="mt-2 text-muted small">';
                    $html .= e($group->creator->name ?? 'System');
                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('actions', function ($group) {

                    $edit = '
        <a href="' . route('members.groups.manage', $group) . '" 
           class="btn btn-sm btn-outline-primary action-btn">
            <i class="fas fa-cog me-1"></i> Manage
        </a>';

                    $delete = '
        <form action="' . route('members.groups.destroy', $group) . '" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm(\'Delete this group?\');">'
                        . csrf_field() . method_field('DELETE') . '
            <button type="submit" 
                    class="btn btn-sm btn-outline-danger action-btn">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </form>';

                    return '<div class="d-flex justify-content-end gap-2">'
                        . $edit . $delete .
                        '</div>';
                })
                ->rawColumns(['members', 'actions'])
                ->make(true);
        }

        return view('members.groups.index');
    }

    /**
     * Show form to create a new group
     */
    public function create()
    {
        return view('members.groups.create');
    }

    /**
     * Store a new group
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('members.groups.index')->with('success', 'Group created!');
    }

    /**
     * Show manage page for a group
     */
    public function manage(Group $group)
    {
        $group->load('members');
        return view('members.groups.manage', compact('group'));
    }


    public function membersList(Group $group, Request $request)
    {
        // $group is already a model instance (thanks to route model binding)
        $query = $group->members()->select('members.*'); // ✅ members() works here

        return DataTables::of($query)
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
            })->addColumn('actions', function ($member) use ($group) {
                return '
            <form action="' . route('members.groups.removeMember', [$group, $member]) . '" method="POST"
                onsubmit="return confirm(\'Remove this member?\');" class="d-inline">
                ' . csrf_field() . method_field('DELETE') . '
                <button class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-user-minus me-1"></i> Remove
                </button>
            </form>';
            })
            ->rawColumns(['actions', 'members'])
            ->make(true);
    }


    /**
     * Update a group
     */
    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group->update($request->only('name', 'description'));

        // Use ToastMagic instead of session flash
        ToastMagic::success("Updated!", "Group details have been updated successfully.");

        // Redirect back to the manage page
        return redirect()->route('members.groups.manage', $group);
    }
    /**
     * Delete a group
     */

    public function destroy(Group $group)
    {
        MemberGroup::where('group_id', $group->id)->delete();
        $group->delete();

        ToastMagic::success("Deleted!", "The group has been deleted successfully.");
        return redirect()->route('members.groups.index');
    }

    public function invite(Request $request, Group $group)
    {
        $request->validate([
            'no_ahli' => 'required|exists:members,no_ahli',
        ]);

        $user = Member::where('no_ahli', $request->no_ahli)->first();

        if (!$user) {
            ToastMagic::error("Oops!", "Member not found.");
            return back();
        }

        $group->members()->syncWithoutDetaching([$user->id]);

        ToastMagic::success("Added!", "Member has been added to the group.");
        return back();
    }

    public function removeMember(Group $group, Member $member)
    {
        $group->members()->detach($member->id);

        ToastMagic::success("Removed!", "Member has been removed from the group.");
        return back();
    }
}