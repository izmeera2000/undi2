<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SystemMail;
use App\Models\Email;
use Mail;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class MailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $to = $request->to;
        $subject = $request->subject;
        $body = $request->body;

        // Send the email
        Mail::to($to)->send(new SystemMail($subject, $body));

        // Save to DB
        Email::create([
            'from' => config('mail.from.address'),
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'direction' => 'sent',
            'email_date' => now(),
        ]);


    ToastMagic::info("Success!", "Your data has been saved!");

        return redirect()
            ->back()
             ;
    }
}
