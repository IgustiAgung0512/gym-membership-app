<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $member = $request->user()->member()->with('package', 'rfidCard')->firstOrFail();
        $attendances = $member->attendances()->latest('check_in_at')->limit(10)->get();
        $totalVisits = $member->attendances()->count();
        $visitsThisMonth = $member->attendances()->whereMonth('check_in_at', now()->month)->count();

        return view('member.dashboard', compact('member', 'attendances', 'totalVisits', 'visitsThisMonth'));
    }
}
