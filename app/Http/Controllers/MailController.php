<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SystemMail;
use App\Models\Email;
use Illuminate\Support\Facades\Mail;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class MailController extends Controller
{
    public function sendEmail(Request $request)
    {
        // Validate input
        $data = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        try {
            // Send the email
            Mail::to($data['to'])->send(new SystemMail($data['subject'], $data['body']));

            // Save to database
            Email::create([
                'from' => config('mail.from.address'),
                'to' => $data['to'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'direction' => 'sent',
                'email_date' => now(),
            ]);

            ToastMagic::success("Email Sent!", "Your email has been sent successfully.");

        } catch (\Exception $e) {
            ToastMagic::error("Error!", "Failed to send email: " . $e->getMessage());
        }

        return redirect()->back();
    }
}
