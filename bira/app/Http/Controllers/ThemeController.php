<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    private const VALID_THEMES = ['dark', 'violet-day', 'green', 'wood'];

    public function index()
    {
        return view('themes.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:' . implode(',', self::VALID_THEMES),
        ]);

        Auth::user()->update(['theme' => $validated['theme']]);

        return back()->with('success', 'Theme updated.');
    }
}
