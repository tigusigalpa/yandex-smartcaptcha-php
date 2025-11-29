<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tigusigalpa\YandexSmartCaptcha\Laravel\Facades\SmartCaptcha;

/**
 * Example Laravel controller with SmartCaptcha validation
 */
class ContactController extends Controller
{
    /**
     * Show contact form
     */
    public function show()
    {
        return view('contact', [
            'clientKey' => config('smartcaptcha.client_key'),
        ]);
    }
    
    /**
     * Process contact form
     */
    public function submit(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:1000',
            'smart-token' => 'required|string',
        ]);
        
        // Validate captcha token
        try {
            $result = SmartCaptcha::validate(
                $validated['smart-token'],
                config('smartcaptcha.secret_key'),
                $request->ip()
            );
            
            if (!$result->isValid()) {
                return back()
                    ->withErrors(['smart-token' => 'Captcha validation failed'])
                    ->withInput();
            }
            
            // Process form (send email, save to database, etc.)
            // ...
            
            return redirect()
                ->route('contact.success')
                ->with('success', 'Your message has been sent!');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['smart-token' => 'Captcha validation error'])
                ->withInput();
        }
    }
}
