<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Member;
use App\Models\MemberGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GroupController extends Controller
{
    /**
     * List all groups with members (DataTables AJAX)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $groups = Group::with('members');

            return DataTables::of($groups)
                ->addIndexColumn()
                ->addColumn('members', function ($group) {
                    // Render member list with remove buttons
                    $html = '<ul class="mb-0 ps-3">';
                    foreach ($group->members as $member) {
                        $html .= '<li class="d-flex justify-content-between align-items-center">';
                        $html .= e($member->name ?? $member->email ?? 'Unnamed Member');
                        $html .= '<form action="'.route('members.groups.removeMember', [$group, $member]).'" method="POST" class="d-inline ms-2" onsubmit="return confirm(\'Remove this member?\');">';
                        $html .= csrf_field().method_field('DELETE');
                        $html .= '<button class="btn btn-sm btn-outline-danger">Remove</button></form>';
                        $html .= '</li>';
                    }
                    $html .= '</ul>';

                    // Invite form
                    $html .= '<form action="'.route('members.groups.invite', $group).'" method="POST" class="d-flex mt-2">';
                    $html .= csrf_field();
                    $html .= '<input type="email" name="email" class="form-control form-control-sm me-2" placeholder="Member Email" required>';
                    $html .= '<button class="btn btn-sm btn-success" type="submit">Invite</button>';
                    $html .= '</form>';

                    return $html;
                })
                ->addColumn('actions', function ($group) {
                    $edit = '<a href="'.route('members.groups.edit', $group).'" class="btn btn-sm btn-warning mb-1"><i class="fas fa-edit"></i> Edit</a>';
                    $delete = '<form action="'.route('members.groups.destroy', $group).'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this group?\');">'
                        .csrf_field().method_field('DELETE')
                        .'<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button></form>';
                    return $edit.' '.$delete;
                })
                ->rawColumns(['members','actions'])
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

        Group::create($request->only('name', 'description'));

        return redirect()->route('members.groups.index')->with('success', 'Group created!');
    }

    /**
     * Show form to edit a group
     */
    public function edit(Group $group)
    {
        return view('members.groups.edit', compact('group'));
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

        return redirect()->route('members.groups.index')->with('success', 'Group updated!');
    }

    /**
     * Delete a group
     */
    public function destroy(Group $group)
    {
        // Remove all pivot entries first
        MemberGroup::where('group_id', $group->id)->delete();
        $group->delete();

        return redirect()->route('members.groups.index')->with('success', 'Group deleted!');
    }

    /**
     * Invite/add a member by email
     */
    public function invite(Request $request, Group $group)
    {
        $request->validate([
            'email' => 'required|email|exists:members,email',
        ]);

        $member = Member::where('email', $request->email)->first();

        // Use MemberGroup pivot directly
        MemberGroup::firstOrCreate([
            'group_id' => $group->id,
            'member_id' => $member->id
        ]);

        return back()->with('success', 'Member added!');
    }

    /**
     * Remove a member from a group
     */
    public function removeMember(Group $group, Member $member)
    {
        MemberGroup::where('group_id', $group->id)
            ->where('member_id', $member->id)
            ->delete();

        return back()->with('success', 'Member removed!');
    }
}