<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendTestEmail(Request $request)
    {
        $email = $request->input('email');
        $name = $request->input('name', 'User');

        Mail::send('emails.test-email', ['name' => $name], function ($message) use ($email) {
            $message->to($email)
                    ->subject('Test Email from Laravel');
        });

        return response()->json(['message' => 'Email sent successfully']);
    }

}
