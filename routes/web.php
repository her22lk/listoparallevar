<?php
 //header("Access-Control-Allow-Origin: *");
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


    Route::get('/login-google', function () {
        return Socialite::driver('google')->redirect();
    });

    Route::get('/google-callback', function () {
        $user = Socialite::driver('google')->stateless()->user();
        $userExist = User::where('external_id', $user->id)->where('external_auth', 'google')->first();

        if ($userExist) {
            Auth::login($userExist);
            $token = JWTAuth::fromUser($userExist);
        } else {
            $userNew = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'external_id' => $user->id,
                'external_auth' => 'google',
                'password' => bcrypt(Str::random(16)),
                'type' => 'person'
            ]);
            Auth::login($userNew);
            $token = JWTAuth::fromUser($userNew);
        }

        return redirect('/dashboard')->with('token', $token);
    });


Route::get('/login-facebook', function () {
    return Socialite::driver('facebook')->redirect();
});

Route::get('/facebook-callback', function () {
    $user = Socialite::driver('facebook')->stateless()->user();
    dd($user);
   $userExist = User::where('external_id', $user->id)->where('external_auth','facebook')->first();
if($userExist) {
    Auth::login($userExist);
     $token = JWTAuth::fromUser($userExist);
} else {
    $userNew = User::create([
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->avatar,
        'external_id' => $user->id,
        'external_auth' => 'facebook',

    ]);
    Auth::login($userNew);
     $token = JWTAuth::fromUser($userNew);
}

 return redirect('/dashboard')->with('token', $token);
});

Route::get('/reset-password',[UserController::class, 'resetPasswordLoad']);
Route::post('/reset-password',[UserController::class, 'resetPassword']);
Route::get('/verify-email/{token}',[UserController::class, 'verificationMail']);


