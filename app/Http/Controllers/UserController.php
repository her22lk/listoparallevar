<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Pack;
use App\Models\Purchase;
use Cloudinary;
class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('api');
    }

    public function getAllUsers()
    {
        try {

            $users = User::with('location')->get();

            $userDetails = $users->map(function ($user) {
            $userLocation = $user->location;


                return [
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'address' => $userLocation ? $userLocation->address : null,
                    'longitude' => $userLocation ? $userLocation->longitude : null,
                    'latitude' => $userLocation ? $userLocation->latitude : null,
                    // 'role' => $user->role,
                    'avatar' => $user->avatar,
                    'external_id' => $user->external_id,
                    'external_auth' => $user->external_auth,
                ];
            });

            return response()->json(['users' => $userDetails]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error while fetching users',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unexpected error while fetching users',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



   public function getUserById($userId)
{
    try {
        $user = User::findOrFail($userId);
        $userLocation = $user->location;

            $user = User::findOrFail($userId);
            $userLocation = $user->location;

                return [
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'address' => $userLocation ? $userLocation->address : null,
                    'longitude' => $userLocation ? $userLocation->longitude : null,
                    'latitude' => $userLocation ? $userLocation->latitude : null,
                    'type' => $user->type,
                    'avatar' => $user->avatar,
                    'external_id' => $user->external_id,
                    'external_auth' => $user->external_auth,
                    'description' => $user->description,
                    'category' => $user->category,
                ];

            return response()->json(['user' => $userDetails]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, User $user)
    {
        try {

            $fields = [
                'name',
                'lastname',
                'email',
                'password',
            ];

            $validations = [
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'min:6',
            ];

            foreach ($fields as $field) {
                if ($request->has($field)) {
                    if (array_key_exists($field, $validations)) {
                        $this->validate($request, [$field => $validations[$field]]);
                    }
                    $user->$field = $request->input($field);
                }
            }

            // Actualizar información de ubicación si está disponible
            if ($request->has('address') || $request->has('latitude') || $request->has('longitude')) {
                if ($user->location) {
                    $user->location->update([
                        'address' => $request->input('address', $user->location->address),
                        'latitude' => $request->input('latitude', $user->location->latitude),
                        'longitude' => $request->input('longitude', $user->location->longitude),
                    ]);
                }
            }

            // Actualizar rol si está disponible
            if ($request->has('role')) {
                $user->syncRoles([$request->input('role')]);
            }

            $user->save();

            return response()->json(['message' => 'User updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            $errors = $validationException->validator->getMessageBag()->toArray();
            return response()->json(['error' => 'Validation error', 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating user', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function avatar(Request $request)
    {
        try {

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png|max:4096',
            ], [
                'image.required' => 'An image is required.',
                'image.image' => 'The uploaded file must be an image.',
                'image.mimes' => 'The image must be in JPEG or PNG format.',
                'image.max' => 'The image size must not exceed 2 MB.',
            ]);

            $uploadedFile = $request->file('image');
            $user = Auth::user();
            $imageUrl = Cloudinary::upload($uploadedFile->getRealPath());
            $user->avatar = $imageUrl->getSecurePath();
            $user->save();
            return response()->json(['message' => $user->avatar]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            $errors = $validationException->validator->getMessageBag()->toArray();
            return response()->json(['error' => 'Validation error', 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating user', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deleteImage($id) {

        try {

            $user = Auth::user();
            $publicId = pathinfo(parse_url($user->avatar, PHP_URL_PATH), PATHINFO_FILENAME);
            Cloudinary::destroy($publicId);
            $user->avatar = null;
            $user->save();
            return response()->json(['photo' => 'user photo deleted successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Invalid ID'], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'user not found'], 404);
        }
    }

    public function profileBusiness ($id)
    {

        $profile = User::find($id);
        $purchases = [];

        $packs = Pack::where('user_id', $id)->get();

        foreach ($packs as $pack)
        {
            $purchase = Purchase::where('pack_id', $pack->id)->get();
            if (!$purchase->isEmpty()) {
                $purchases[] = $purchase;
            }
        }

        return response()->json([
            'profile' => $profile,
            'purchases' => $purchases
        ]);
    }


 public function forgetPassword(Request $request)
{
    try {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(40);
            $domain = URL::to('/');
            $url = $domain . '/reset-password?token=' . $token;

            $data['url'] = $url;
            $data['email'] = $request->email;
            $data['title'] = "Password Reset";
            $data['body'] = "Please click below to reset your password.";

            Mail::send('forgetPasswordMail', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject($data['title']);
            });

            $datetime = now()->format('Y-m-d H:i:s');
            PasswordReset::updateOrCreate(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => $datetime
                ]
            );
            return response()->json(['success' => true, 'message' => 'Password reset email sent']);
        } else {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}

 public function resetPasswordLoad(Request $request)
{
    $resetData = PasswordReset::where('token',$request->token)->first();
    if($resetData ){

       $user = User::where('email', $resetData->email)-> first();
    return view('resetPassword', compact('user'));
    }else{
        return view('notFound');
    }
}
public function resetPassword(Request $request)
{
    $request->validate([
        'password' => 'required|string|min:6|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
    ]);

    $user = User::find($request->input('id'));

    if ($user) {
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return "<h1>Your password has been changed successfully</h1>";
    } else {
        return "<h1>User not found</h1>";
    }
}

public function sendVerifyEmail( Request $request, $email)
{
    $user = User::find($request->input('id'));

    if (auth()->user()) {
        $user = User::where('email', $email)->get();
        if(count($user) > 0){
                $random = Str::random(40);
                $domain = URL::to('/');
                $url = $domain.'/verify-email/'.$random;

            $data ['url'] = $url;
            $data['email'] =$email;
            $data['title']="Email Verification";
            $data['body']="Please click here to verify your email";

            Mail::send('verifyEmail',['data'=> $data],function($message) use ($data){
             $message->to($data['email'])->subject($data['title']);
            });

           $user = User::find($user[0]['id']);
           $user->remember_token = $random;
           $user->save();

           return response()->json(['success'=> true,'msg'=> "Email sent succesfully"]);
        }else{
            return response()->json(['success'=>false,'msg'=>'User is not found']);
    }
        }else {
        return response()->json(['success'=>false,'msg'=>'User is not authenticated']);
    }
}

public function verificationMail( Request $request, $token)
{
        $user = User::where('remember_token', $token)->get();
        if(count($user) > 0){
                $datetime = Carbon::now()->format('Y-m-d H:i:s');
                $user = User::find($user[0]['id']);
                $user ->  remember_token = '';
                $user ->  is_verified = 1;
                $user ->  email_verified_at = $datetime;
                $user-> save();

                return "<h1>Email verified succesfully </h1>";
        }else{
            return view('notFound');
    }
}
}
