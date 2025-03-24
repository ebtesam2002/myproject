<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\AppointmentController; 
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register'); 
    Route::post('/login', 'login');   
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => 'User authenticated successfully!',
            'user' => $request->user(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']); 
    
    Route::post('/change-password', function (Request $request) {
        
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],

            ]);
        }

        
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password changed successfully!',

        ]);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/appointments/book', [AppointmentController::class, 'book']);
    Route::put('/appointments/cancel/{id}', [AppointmentController::class, 'cancel']);
    Route::put('/appointments/reschedule/{id}', [AppointmentController::class, 'reschedule']);
});

Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'adminLogout']);
    Route::post('/admin/change-password', [AuthController::class, 'changeAdminPassword']);
});
