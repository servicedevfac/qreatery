<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RevokeUserAccessAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LogoutAllController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, RevokeUserAccessAction $revokeUserAccess): Response
    {
        $revokeUserAccess->handle($request->user());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::forgetGuards();

        return response()->noContent();
    }
}
