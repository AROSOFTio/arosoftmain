<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormThankYou;
use App\Mail\ContactFormToAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        // Simple honeypot trap. Bots filling this hidden field are silently accepted.
        if (filled($request->input('company'))) {
            return redirect()
                ->route('contact')
                ->with('contact_status', 'Thank you. Your message has been received.');
        }

        $validated = $request->validate(
            [
                'full_name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email:rfc,dns', 'max:180'],
                'phone' => ['nullable', 'string', 'max:40'],
                'subject' => ['required', 'string', 'max:180'],
                'message' => ['required', 'string', 'min:20', 'max:5000'],
            ],
            [
                'full_name.required' => 'Please enter your full name.',
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please use a valid email address.',
                'subject.required' => 'Please enter a subject for your message.',
                'message.required' => 'Please write a message before sending.',
                'message.min' => 'Your message should be at least :min characters.',
            ]
        );

        Mail::to('info@arosoft.io')->send(new ContactFormToAdmin($validated));
        Mail::to($validated['email'])->send(new ContactFormThankYou($validated));

        return redirect()
            ->route('contact')
            ->with('contact_status', 'Thank you. Your message has been sent successfully.');
    }
}
