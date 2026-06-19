<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function dashboard()
    {
        $requests = ServiceRequest::where('user_id', Auth::id())
            ->orderByRaw("CASE WHEN status = 'Ready to Claim' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        $appointments = Appointment::where('user_id', Auth::id())->latest()->get();

        return view('student.dashboard', [
            'requests' => $requests,
            'appointments' => $appointments,
            'notifications' => $requests->where('status', 'Ready to Claim'),
        ]);
    }

    public function storeRequest(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        ServiceRequest::create([
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'reason' => $data['reason'] ?? null,
            'status' => 'Pending',
            'date_requested' => now(),
        ]);

        return back()->with('success', 'Request submitted.');
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required'],
        ]);

        Appointment::create([
            'user_id' => Auth::id(),
            'date' => $data['date'],
            'time' => $data['time'],
            'queue_number' => 'Q-' . random_int(100, 999),
            'status' => 'Scheduled',
        ]);

        return back()->with('success', 'Appointment scheduled.');
    }

    public function changePassword(Request $request)
    {
        if ($request->filled('new_password') && ! $request->filled('password')) {
            $request->merge([
                'password' => $request->input('new_password'),
                'password_confirmation' => $request->input('new_password'),
            ]);
        }

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], Auth::user()->password)) {
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
            }

            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        Auth::user()->update(['password' => $data['password']]);

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
        }

        return back()->with('success', 'Password updated.');
    }
}
