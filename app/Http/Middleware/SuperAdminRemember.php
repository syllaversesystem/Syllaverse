<?php
// -----------------------------------------------------------------------------
// File: app/Http/Middleware/SuperAdminRemember.php
// Description: Restores Super Admin session from a remember-me cookie
// -----------------------------------------------------------------------------

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SuperAdminRemember
{
    /**
     * If a remember cookie exists, ensure the superadmin session flag is set.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $cookie = $request->cookie('superadmin_remember');
            if ($cookie === '1' && !Session::get('is_superadmin')) {
                Session::put('is_superadmin', true);
            }
        } catch (\Throwable $e) {
            // ignore errors restoring remember session
        }
        return $next($request);
    }
}
