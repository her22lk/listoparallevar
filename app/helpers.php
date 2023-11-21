<?php

function checkLogin()
{
    $u = auth()->user();
    if (!$u) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }
}
