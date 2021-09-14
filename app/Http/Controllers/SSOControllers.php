<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SSOControllers extends Controller
{
    public function redirect(Request $request)
    {
        $request->session()->put("state", $state =  Str::random(40));
        $query = http_build_query([
            "client_id" => env('SSO_ID'),
            "redirect_uri" => env('APP_URL')."/callback",
            "response_type" => "code",
            "scope" => "",
            "state" => $state
        ]);
        return redirect(env('SSO_URL')."/oauth/authorize?" . $query);
    }

    public function callback(Request $request)
    {
        $state = $request->session()->pull("state");
        abort_unless(strlen($state) > 0 && $state == $request->state,"500");
        $response = Http::asForm()->post(
            env('SSO_URL')."/oauth/token",
            [
            "grant_type" => "authorization_code",
            "client_id" => env('SSO_ID'),
            "client_secret" => env('SSO_SECRET'),
            "redirect_uri" => env('APP_URL')."/callback",
            "code" => $request->code
        ]);
        $request->session()->put($response->json());
        return redirect("/login_with_sso");
    }

    public function login_with_sso(Request $request)
    {
        // akses API SSO ITK
        $access_token = $request->session()->get("access_token");
        $response = Http::withHeaders([
            "Accept" => "application/json",
            "Authorization" => "Bearer " . $access_token
        ])->get(env('SSO_URL')."/api/user");
        // Data user yang diperoleh
        $user = [
            'name' => $response['name'],
            'email' => $response['email'],
            'sso_id'=> $response['id'],
            'email_verified_at' => $response['email_verified_at'],
            'updated_at' => $response['updated_at'],
        ];
        // Mengecek apakah akun SSO ITK sudah terdaftar atau belum
        $finduser = User::where('sso_id', $response['id'])
                        ->orWhere('email', $response['email'])
                        ->first();
        if($finduser){
            // Menyamakan versi data akun SSO ITK dengan akun SSO ITK yang sudah didaftarkan sebelumnya
            if ($finduser->updated_at != $response['updated_at'] or $finduser->email == $response['email'] and $finduser->sso_id == null) {
                $user['password'] = $finduser->password;
                $finduser->update($user);
                Auth::login($finduser);
            }
            else {
                // Login degnan akun SSO ITK
                Auth::login($finduser);
            }
            // Redirect setelah login
            return redirect('/dashboard');

        }else{
            // Kalau akun SSO ITK belum terdaftar maka kita daftarkan dulu
            $user['password'] = encrypt('');
            $newUser = User::create($user);
            $newUser->save();
            // Login sebagai User baru
            Auth::login($newUser);
            // Redirect setelah login
            return redirect('/dashboard');
        }
    }
}
