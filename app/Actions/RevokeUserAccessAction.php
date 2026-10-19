<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RevokeUserAccessAction
{
    public function handle(User $user, ?string $exceptSessionId = null): void
    {
        $user->tokens()->delete();

        if (config('session.driver') !== 'database') {
            return;
        }

        $sessions = DB::table((string) config('session.table', 'sessions'))
            ->where('user_id', $user->getAuthIdentifier());

        if ($exceptSessionId !== null) {
            $sessions->where('id', '!=', $exceptSessionId);
        }

        $sessions->delete();
    }
}
