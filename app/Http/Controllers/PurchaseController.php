<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\Pack;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function store(Request $request){
        try {

            DB::beginTransaction();
            $validatedData = $request->validate([
                'seller_id' => 'required|exists:users,id',
                'pack_id' => 'required|exists:packs,id',
                'payment_id' => 'nullable|exists:payments,id',
                'amount' => 'required|numeric|min:0',
                'credit_card_number' => 'numeric',
                'cvv' => 'numeric',
                'expiration_date' => 'string',
                'price' => 'required|numeric'
            ]);
            $pack = Pack::findOrFail($validatedData['pack_id']);

            if ($pack->stock < $validatedData['amount'])
            {
                throw new \Exception('not enough stock');
            }
            $user = Auth::user();
            if ($user->hasRole('business')) 
            {
                return response()->json(['error' => 'Invalid role, must be a customer to purchase'], 400);
            }
            if(!isset($validatedData['payment_id']))
            {
                $payment = Payment::create([
                    'user_id' => Auth::user()->id,
                    'amount' => $validatedData['amount'],
                    'credit_card_number'=> $validatedData['credit_card_number'] ?? null,
                    'cvv'=> $validatedData['cvv'] ?? null,
                    'expiration_date'=>$validatedData['expiration_date'] ?? null
                ]);
            }
            $encryptedId = Auth::user()->getAuthIdentifier();

            

            $purchase = Purchase::create([
                'pack_id' => $validatedData['pack_id'],
                'seller_id' => $validatedData['seller_id'],
                'user_id' => $encryptedId,
                'code' => $this->generateCode(),
                'amount' => $validatedData['price'],
                'payment_id' => $validatedData['payment_id'] ?? $payment->id
            ]);

            $pack->stock -= $validatedData['amount'];
            $pack->save();
            DB::commit();
            return response()->json(['Purchase created' => $purchase], 201);
        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(Request $request){
        try {
            $currentUser = Auth::user();
            //$encryptedId = Auth::user()->getAuthIdentifier();

            if($currentUser->type === 'business'){
                $purchase = Purchase::where('seller_id',$currentUser->id)
                ->with(['user', 'pack','seller'])
                ->get();
            } else {
                $purchase = Purchase::where('user_id',$currentUser->id)
                ->with(['user', 'pack','seller'])
                ->get();
            }

            return response()->json(['Purchases' => $purchase], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    public function showbyid(Request $request,$id){
        try {
            $currentUser = Auth::user();
            //$encryptedId = Auth::user()->getAuthIdentifier();

            if($currentUser->type === 'business'){
                $purchase = Purchase::where('id',$id)
                ->with(['user', 'pack','seller'])
                ->get();
            } else {
                $purchase = Purchase::where('id',$id)
                ->with(['user', 'pack','seller'])
                ->get();
            }

            return response()->json(['Purchase:' => $purchase], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }
    public function update(Request $request){
        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:purchases,id',
                'status' => 'required|string|max:255'
            ]);
            $purchase = Purchase::where('id', $validatedData['id'])->update(['status' => $validatedData['status']]);

            return response()->json(['Purchase' => $purchase], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalidated data',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    public function destroy($id){
        $purchase = Purchase::find($id);
        if($purchase){
            $purchase->delete();
            return response()->json(['message' => 'Purchase deleted'], 200);
        }else{
            return response()->json(['message' => 'Purchase not found'], 404);
        }
    }
    public function generateCode()
    {
        return substr(uniqid(), -6);
    }
}
