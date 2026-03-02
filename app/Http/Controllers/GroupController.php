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
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->members_id) {
            ToastMagic::error("Oops!", "User has no linked member record.");
            return back();
        }

        $memberId = $user->members_id;
        $group->members()->syncWithoutDetaching([$memberId]);

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