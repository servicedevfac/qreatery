<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RevokeUserAccessAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ChangePasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ChangePasswordRequest $request, RevokeUserAccessAction $revokeUserAccess): Response
    {
        $user = $request->user();
        $user->forceFill([
            'password' => $request->validated('password'),
            'remember_token' => Str::random(60),
        ])->save();

        $revokeUserAccess->handle($user, $request->session()->getId());

        return response()->noContent();
    }
}
