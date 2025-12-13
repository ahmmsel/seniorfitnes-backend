<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string',
        ]);

        $user = $request->user();

        $token = $request->input('token');
        $platform = $request->input('platform');

        $device = DeviceToken::updateOrCreate(
            ['token' => $token],
            ['user_id' => $user->id, 'platform' => $platform, 'last_used_at' => now()]
        );

        return response()->json(['success' => true, 'device_token_id' => $device->id], 201);
    }

    public function destroyById(Request $request, $id)
    {
        $user = $request->user();
        $device = DeviceToken::where('id', $id)->where('user_id', $user->id)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $device->delete();
        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $user = $request->user();
        $deleted = DeviceToken::where('token', $request->input('token'))->where('user_id', $user->id)->delete();
        return response()->json(['success' => (bool) $deleted]);
    }
}
