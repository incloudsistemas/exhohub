<?php

namespace App\Http\Middleware;

use App\Filament\Pages\System\PendingUserBlockPage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PendingUserBlockAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && (int) $user->status->value === 2) {
            $url = PendingUserBlockPage::getUrl();

            if ($request->url() !== $url) {
                return redirect($url);
            }
        }

        return $next($request);
    }
}
