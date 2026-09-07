<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers to the response.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah Clickjacking (agar halaman web tidak bisa di-embed di iframe situs lain)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Mencegah MIME Type Sniffing (mencegah eksekusi file berbahaya yang disamarkan)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Proteksi XSS bawaan browser modern
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Mencegah kebocoran URL asal / parameter sensitif pada link eksternal
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Membatasi akses fitur hardware sensitif yang tidak digunakan oleh web
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Hapus header identitas server/PHP jika terpasang (obfuscation)
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
