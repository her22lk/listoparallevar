<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try{

            $validateData = $request->validate([
                'address' => 'required',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'postal_code' => 'required',
                'city' => 'required',
                'province' => 'required'
            ]);
            $user = Auth::user();

            $location = Location::find($user->location_id);
            $location->update([
                'address' => $validateData['address'],
                'latitude' => $validateData['latitude'],
                'longitude' => $validateData['longitude'],
                'postal_code' => $validateData['postal_code'],
                'city' => $validateData['city'],
                'province' => $validateData['province']

            ]);
            $user->location_id = $location->id;
            $user->save();
            return response()->json(['location' => $location]);
            } catch (ValidationException $e){
                return response()->json([
                    'error' => 'Invalid data',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 400);
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        //
    }
}
