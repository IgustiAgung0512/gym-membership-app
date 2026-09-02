<?php

namespace App\Http\Controllers;

use App\Models\Scan_UID;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScanUIDController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => ['required', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $scan = Scan_UID::create([
            'uid' => $request->input('uid'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'UID berhasil disimpan',
            'data' => $scan,
        ], 201);
    }
}