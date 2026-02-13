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
        $query = Member::query();

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('members', function ($row) {
                return '
                <div class="d-flex align-items-center gap-3">
                    <img src="' . '$row->profile->getProfilePictureUrlAttribute()' . '" 
                         class="rounded-circle" width="40">
                    <div>
                        <a href="' . ' ' . '" class="fw-semibold">
                            ' . 'e($row->name)' . '
                        </a>
                        <div class="text-muted small">' .' e($row->email)' . '</div>
                    </div>
                </div>';
            })

            ->addColumn('role', function ($row) {
                return '<span class="badge bg-info">' . 'ucfirst($row->role)' . '</span>';
            })

            ->addColumn('joined', function ($row) {
                return $row->created_at->format('d M Y');
            })

            ->addColumn('actions', function ($row) {
                return '
                <div class="btn-group">
                    <div class="btn-group">
                        <a href="' . '' . '" class="btn btn-sm btn-light" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
 
                       
                    </div>
                </div>';
            })

            ->rawColumns(['members', 'role', 'actions'])
            ->make(true);
    }
}
