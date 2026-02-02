<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Jobs\TransferMembersJob;

class MembersTransferController extends Controller
{
    public function transfer()
    {
        TransferMembersJob::dispatch();

        return back()->with(
            'success',
            'Proses pemindahan sedang berjalan di background'
        );
    }
}
