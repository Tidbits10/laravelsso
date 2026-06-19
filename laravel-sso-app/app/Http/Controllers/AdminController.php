<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\MockSis;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard()
    {
        $requests = ServiceRequest::with('user')
            ->orderByRaw("CASE WHEN status = 'Pending' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        return view('admin.dashboard', [
            'requests' => $requests,
            'queue' => Appointment::with('user')->latest()->get(),
            'logs' => AuditLog::latest()->limit(10)->get(),
            'stats' => [
                'total' => $requests->count(),
                'pending' => $requests->where('status', 'Pending')->count(),
                'approved' => $requests->where('status', 'Approved')->count(),
                'ready' => $requests->where('status', 'Ready to Claim')->count(),
                'rejected' => $requests->where('status', 'Rejected')->count(),
            ],
        ]);
    }

    public function approve(Request $request, ServiceRequest $serviceRequest)
    {
        $remarks = $request->input('remarks', 'All requirements met.');
        $qrText = 'PUP-SP-VERIFIED-REQ-' . $serviceRequest->id . '-' . Str::upper(Str::random(10));

        $serviceRequest->update([
            'status' => 'Approved',
            'qr_text' => $qrText,
            'qr_image' => 'https://quickchart.io/qr?size=220&text=' . urlencode($qrText),
            'remarks' => $remarks,
        ]);

        $this->log("Approved request #{$serviceRequest->id}");
        $this->notifyStudent($serviceRequest, 'Document Request Approved', "Your {$serviceRequest->type} request was approved. Remarks: {$remarks}");

        return $this->actionResponse($request, 'Request approved successfully!');
    }

    public function reject(Request $request, ServiceRequest $serviceRequest)
    {
        $remarks = $request->input('remarks', 'Incomplete requirements.');

        $serviceRequest->update([
            'status' => 'Rejected',
            'remarks' => $remarks,
        ]);

        $this->log("Rejected request #{$serviceRequest->id}");
        $this->notifyStudent($serviceRequest, 'Document Request Rejected', "Your {$serviceRequest->type} request was rejected. Reason: {$remarks}");

        return $this->actionResponse($request, 'Request rejected.');
    }

    public function markReady(Request $request, ServiceRequest $serviceRequest)
    {
        $serviceRequest->update(['status' => 'Ready to Claim']);

        $this->log("Marked request #{$serviceRequest->id} as Ready to Claim");
        $this->notifyStudent($serviceRequest, 'Document Ready to Claim', "Your {$serviceRequest->type} is ready to claim.");

        return $this->actionResponse($request, 'Document marked ready to claim.');
    }

    public function serveQueue(Request $request, Appointment $appointment)
    {
        $appointment->update(['status' => 'Completed']);
        $this->log("Served queue ticket {$appointment->queue_number}");

        return $this->actionResponse($request, 'Queue ticket completed.');
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => ['required', 'file']]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $headers = array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', trim($header));

            return strtolower(str_replace([' ', '-'], '_', $header));
        }, fgetcsv($handle) ?: []);

        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }

            $record = array_combine($headers, $row);
            $studentNumber = trim($record['student_number'] ?? $record['student_no'] ?? $record['student_id'] ?? '');
            $name = trim($record['name'] ?? $record['student_name'] ?? $record['full_name'] ?? '');
            $email = strtolower(trim($record['email'] ?? $record['email_address'] ?? ''));

            if ($studentNumber !== '' && $name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                MockSis::updateOrCreate(
                    ['student_number' => $studentNumber],
                    ['name' => $name, 'email' => $email]
                );
                $imported++;
            }
        }

        fclose($handle);
        $this->log("Imported {$imported} SIS master list rows");

        return back()->with('success', "SIS master list imported. {$imported} row(s) saved.");
    }

    public function exportCsv(): StreamedResponse
    {
        $this->log('Exported monthly report');

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Request ID', 'Student ID', 'Student Name', 'Document Type', 'Status', 'Remarks', 'Date Requested']);

            ServiceRequest::with('user')->orderBy('id')->each(function ($request) use ($output) {
                fputcsv($output, [
                    $request->id,
                    $request->user->student_number,
                    $request->user->name,
                    $request->type,
                    $request->status,
                    $request->remarks,
                    optional($request->date_requested)->format('Y-m-d H:i'),
                ]);
            });

            fclose($output);
        }, 'SSO_Monthly_Report.csv');
    }

    private function log(string $action): void
    {
        AuditLog::create([
            'admin_username' => Auth::user()->username ?? Auth::user()->email,
            'action' => $action,
        ]);
    }

    private function notifyStudent(ServiceRequest $serviceRequest, string $subject, string $body): void
    {
        $serviceRequest->loadMissing('user');

        try {
            Mail::raw("Good day, {$serviceRequest->user->name}.\n\n{$body}", function ($message) use ($serviceRequest, $subject) {
                $message->to($serviceRequest->user->email)->subject($subject);
            });

            $this->log("Email sent to {$serviceRequest->user->email}: {$subject}");
        } catch (\Throwable $exception) {
            Log::warning('Student notification email failed.', [
                'request_id' => $serviceRequest->id,
                'email' => $serviceRequest->user->email,
                'error' => $exception->getMessage(),
            ]);

            $this->log("Email failed for {$serviceRequest->user->email}: {$subject}");
        }
    }

    private function actionResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
