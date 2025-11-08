<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiagController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->isStarted()) {
            session()->start();
        }

        session()->put('diag_time', now()->toDateTimeString());

        return response()->json([
            'timestamp' => now()->toDateTimeString(),
            'https'     => $request->isSecure(),
            'forwarded_proto' => $request->header('x-forwarded-proto'),
            'session_id' => session()->getId(),
            'session_value' => session('diag_time'),
            'cookies'   => $request->cookies->all(),
            'headers'   => $request->headers->all(),
            'env'       => [
                'APP_ENV' => env('APP_ENV'),
                'APP_URL' => env('APP_URL'),
                'SESSION_DOMAIN' => env('SESSION_DOMAIN'),
                'SESSION_SECURE_COOKIE' => env('SESSION_SECURE_COOKIE'),
            ],
        ]);
    }

    public function cookieTest()
    {
        return response()->json(['cookie' => 'sent'])
            ->cookie(
                'probe',
                'ok',
                10,
                '/',
                null,
                true,
                true,
                false,
                'None'
            );
    }
}
