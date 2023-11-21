<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{

    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(){


        $encryptedId = Auth::user()->getAuthIdentifier();
        $favorite = Favorite::where('user_id', $encryptedId)
        ->join('users', 'favorites.business_id', '=', 'users.id')
        ->with('user')
        ->get();

        if($favorite){
            return response()->json(['Favorite' => $favorite], 200);
        }else{
            return response()->json(['message' => 'Favorite not found'], 404);
        }
    }

    public function store(Request $request){

        try {

            $validatedData = $request->validate([
                'business_id' => 'required|numeric',
            ]);
            $encryptedId = Auth::user()->getAuthIdentifier();
            $favorite = Favorite::create([
                'user_id' => $encryptedId,
                'business_id' => $validatedData['business_id'],
            ]);

            return response()->json(['Favorite created' => $favorite], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    public function destroy($id){

        
        $encryptedId = Auth::user()->getAuthIdentifier();
        $favorite = Favorite::Where('user_id',$encryptedId)
        ->Where('business_id',$id);

        if($favorite){
            $favorite->delete();
            return response()->json(['message' => 'Favorite deleted'], 200);
        }else{
            return response()->json(['message' => 'Favorite not found'], 404);
        }
    }
}
