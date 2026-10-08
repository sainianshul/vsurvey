<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\NurseProfile;
use App\Exceptions\Nurse\NurseNotApprovedException;

class EnsureNurseIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isNurse()) {
            $profile = $user->nurseProfile;

            // Only allow if profile exists and status is APPROVED
            if (!$profile || $profile->status !== NurseProfile::STATUS_APPROVED) {
                throw new NurseNotApprovedException();
            }
        }

        return $next($request);
    }
}
