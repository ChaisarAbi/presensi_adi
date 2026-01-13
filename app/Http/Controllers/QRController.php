<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QR;

class QRController extends Controller
{
    /**
     * Generate QR Code for today (admin only)
     */
    public function generate()
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat generate QR Code.');
        }

        // Delete expired QR codes
        QrCode::where('expired_at', '<', now())->delete();

        // Generate new token
        $token = bin2hex(random_bytes(16));
        $expiredAt = now()->addHours(2); // QR valid for 2 hours

        // Save to database
        QrCode::create([
            'token' => $token,
            'expired_at' => $expiredAt,
        ]);

        // Generate QR code image
        $qrCode = QR::size(300)->generate($token);

        return view('qr.generate', compact('qrCode', 'token', 'expiredAt'));
    }

    /**
     * Show current active QR codes
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat melihat QR Codes.');
        }

        $qrCodes = QrCode::orderBy('created_at', 'desc')
            ->paginate(10);

        return view('qr.index', compact('qrCodes'));
    }

    /**
     * Validate QR token (API endpoint for scanner)
     */
    public function validateToken(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => 'Token tidak valid.',
            ], 400);
        }

        $qrCode = QrCode::where('token', $request->token)
            ->where('expired_at', '>', now())
            ->first();

        if ($qrCode) {
            return response()->json([
                'valid' => true,
                'message' => 'QR Code valid.',
                'expired_at' => $qrCode->expired_at,
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'QR Code tidak valid atau sudah kadaluarsa.',
        ], 400);
    }
}
