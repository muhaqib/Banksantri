<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Handle registration submission.
     */
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        // For now, return success response
        // In production, you would save to database
        return response()->json([
            'success' => true,
            'message' => 'Registration submitted successfully',
        ], 201);
    }

    /**
     * Handle contact form submission.
     */
    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // For now, return success response
        // In production, you would send email or save to database
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
        ], 201);
    }
}
