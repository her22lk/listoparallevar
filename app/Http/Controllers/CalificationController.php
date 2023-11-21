<?php

namespace App\Http\Controllers;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\Calification;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CalificationController extends Controller
{
    public function index($id)
    {
        try{
            $califications = Calification::where('user_id', $id)->get();
            return response()->json($califications);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    public function store(Request $request)
    {
        try{
             $validatedData = $request->validate([
                'purchase_id' => 'required|exists:purchases,id',
                'stars' => 'required|integer|between:1,5',
                'comment' => 'nullable|string',
                'tags' => 'nullable|required',
                'name' => 'nullable|string',
            ]); 
            $user = Auth::user();
            $purchase = Purchase::findOrFail($validatedData['purchase_id']);

            $calification = Calification::create([
                'user_id' => $user->id,
                'stars' => $validatedData['stars'],  
                'comment' => $validatedData['comment'],
                'tags' => json_encode( $validatedData['tags']),  
                'name' => $validatedData['name'] 
            ]);
            $calification->save();

            $user->type === "business" ? $purchase->feedback_received_id = $calification->id : $purchase->calification_gived_id = $calification->id;
            $purchase->save();

            $user->total_operations += 1;
            $user->total_score += $validatedData['stars'];
            $user->score = $user->total_score / $user->total_operations;
            $user->save();

            return response()->json(['calification'=>$calification], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $validatedData = $request->validate([
                'user_id' => 'exists:users,id',
                'stars' => 'integer|between:1,5',
                'comment' => 'nullable|string',
                'tags' => 'nullable|string',
                'name' => 'nullable|string'
            ]);

            $calification = Calification::find($id);

            if (!$calification) {
                return response()->json(['message' => 'Calificación no encontrada'], 404);
            }

            $calification->update($validatedData);

            return response()->json($calification, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }
}
