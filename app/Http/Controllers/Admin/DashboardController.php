<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard stub (D-17, D-19).
     *
     * Passes the authenticated user so the view can render:
     *   - Greeting: "Bonjour Pierre,"
     *   - User pill: name + role
     * Phase 2 will add real stats (passages, clients, etc.).
     */
    public function index(Request $request)
    {
        return view('admin.dashboard', [
            'user' => auth()->user(),
        ]);
    }
}
