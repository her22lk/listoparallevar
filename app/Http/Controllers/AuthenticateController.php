<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;


class AuthenticateController extends Controller
{
        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        try{
            DB::beginTransaction();
            $validateData = $request->validate([
                'name' => 'required',
                'lastname' => 'nullable',
                'email' => 'required|email|unique:users',
                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                ],
                'type' => 'required',
                'description' => 'nullable',
                'category' => 'nullable'
            ]);

            $user = User::create([
                'name' => $validateData['name'],
                'lastname' => $validateData['lastname'] ?? null,
                'email' => $validateData['email'],
                'password' => bcrypt($validateData['password']),
                'type' => $validateData['type'],
                'description' => $validateData['description'] ?? null,
                'category' => $validateData['category'] ?? null,
            ]);

            $location = Location::create([]);
            $user->location_id = $location->id;
            $user->assignRole($validateData['type']);
            $user->save();
            DB::commit();
        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Invalid data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Invalid data'], 400);
        }
        if ($user) {
            return response()->json([
                'message' => 'User created',
                'token' => $token,
                'item' => $user
            ], 201);
        } else {
            return response()->json([
                'message' => 'User not created',
            ], 400);
        }

    }

    public function login()
    {
        try {
            $credentials = request(['email', 'password']);

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Invalid data'], 400);
            }

            $cookie = cookie('jwt_token', $token, 60, null, null, true, true);
            $user = auth()->user();
            $user->load('location');
            return response()->json([
                'token' => $token,
                'user' => $user,
            ])->withCookie($cookie);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json(['error' => 'Authentication failed', 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Error creating token', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function me()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'message' => 'User found',
            'user' => $user
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        // Invalidar la cookie
        $cookie = Cookie::forget('jwt_token');

        return response()->json(['message' => 'Successfully logged out'])->withCookie($cookie);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    //Funcion para verificar si el token del usuario es valido
    public function verifyToken(Request $request)
    {
        $token = $request->cookie('jwt_token');

        if ($token) {
            return response()->json([
                'message' => 'Token is valid'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Token is invalid'
            ], 400);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    protected function update()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $rules = [
            'name' => 'string',
            'lastname' => 'string',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'password' => [
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
            'type' => 'required',
        ];

        $validator = Validator::make(request()->only(array_keys($rules)), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingEmail = DB::table('users')->where('email', $data['email'])->first();
            if ($existingEmail) {
                return response()->json([
                    'message' => 'The email already exists'
                ], 401);
            }
        }

        if (isset($data['password']) && $data['password'] !== $user->password) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated',
            'user' => $user
        ], 200);
}

    protected function delete()
    {
        $user = auth()->user()->id;

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        DB::table('users')->where('id', $user)->delete();
        $cookie = Cookie::forget('jwt_token');

        return response()->json([
            'message' => 'User deleted'
        ])->withCookie($cookie);

    }
}
