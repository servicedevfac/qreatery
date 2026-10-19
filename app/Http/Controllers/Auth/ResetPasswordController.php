<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RevokeUserAccessAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ResetPasswordRequest $request, RevokeUserAccessAction $revokeUserAccess): JsonResponse
    {
        $status = Password::reset(
            $request->safe()->only(['email', 'password', 'password_confirmation', 'token']),
            function (User $user, string $password) use ($revokeUserAccess): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $revokeUserAccess->handle($user);
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PasswordReset) {
            return response()->json([
                'message' => 'Le lien de réinitialisation est invalide ou a expiré.',
            ], 422);
        }

        return response()->json(['message' => 'Le mot de passe a été réinitialisé.']);
    }
}
