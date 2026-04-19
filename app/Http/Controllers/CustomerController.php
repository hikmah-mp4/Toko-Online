<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Models\Customer; 
use App\Models\User; 
use Illuminate\Support\Facades\Auth; 
use Laravel\Socialite\Facades\Socialite; 
use Illuminate\Support\Facades\Hash; 
 
class CustomerController extends Controller 
{ 
    // Redirect ke Google 
    public function redirect() 
    { 
        return Socialite::driver('google')->redirect(); 
    } 
 
    // Callback dari Google 
    public function callback()
{
    try {
        $socialUser = Socialite::driver('google')->user();

        // cek user
        $registeredUser = User::where('email', $socialUser->email)->first();

        if (!$registeredUser) {

            // buat user
            $user = User::create([
                'nama' => $socialUser->name,
                'email' => $socialUser->email,
                'hp' => '-',
                'role' => 2,
                'status' => 1,
                'password' => Hash::make('12345678'),
            ]);

            // 🔥 buat customer (SIMPAN KE VARIABEL)
            $customer = Customer::create([
                'user_id' => $user->id,
                'google_id' => $socialUser->id ?? null,
                'google_token' => $socialUser->token ?? null,
            ]);

            // 🔍 DEBUG
            // dd($customer);

            Auth::login($user);

        } else {

            // 🔥 CEK apakah customer sudah ada
            $customer = Customer::where('user_id', $registeredUser->id)->first();

            if (!$customer) {
                Customer::create([
                    'user_id' => $registeredUser->id,
                    'google_id' => $socialUser->id ?? null,
                    'google_token' => $socialUser->token ?? null,
                ]);
            }

            Auth::login($registeredUser);
        }

        return redirect()->route('beranda');

    } catch (\Exception $e) {
        dd($e->getMessage()); // biar error keliatan
    }
}
 
    public function logout(Request $request) 
    { 
        Auth::logout(); // Logout pengguna 
        $request->session()->invalidate(); // Hapus session 
        $request->session()->regenerateToken(); // Regenerate token CSRF 
 
        return redirect('/')->with('success', 'Anda telah berhasil logout.'); 
    } 
} 