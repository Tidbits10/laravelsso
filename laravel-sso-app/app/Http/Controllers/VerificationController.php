<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function scanner()
    {
        return view('admin.scanner');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'qr_text' => ['required', 'string'],
        ]);

        $serviceRequest = ServiceRequest::with('user')->where('qr_text', $data['qr_text'])->first();

        if (! $serviceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'WARNING: Document not found or forged.',
            ]);
        }

        return response()->json([
            'success' => true,
            'request' => [
                'id' => $serviceRequest->id,
                'student_number' => $serviceRequest->user->student_number,
                'name' => $serviceRequest->user->name,
                'type' => $serviceRequest->type,
                'status' => $serviceRequest->status,
                'remarks' => $serviceRequest->remarks,
            ],
        ]);
    }
}
