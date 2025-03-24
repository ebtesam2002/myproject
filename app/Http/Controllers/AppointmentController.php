<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    
    public function book(Request $request)
    {
        $request->validate([
            'appointment_date' => 'required|date|after:today',
        ]);

        
        $existingAppointment = Appointment::where('appointment_date', $request->appointment_date)
                                          ->where('status', 'booked')
                                          ->first();
        
        if ($existingAppointment) {
            return response()->json(['error' => 'The selected appointment date is already booked'], 409);
        }

        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'appointment_date' => $request->appointment_date,
            'status' => 'booked',
        ]);

        return response()->json(['message' => 'Appointment booked successfully', 'appointment' => $appointment]);
    }

     
    public function cancel($id)
    {
        $appointment = Appointment::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Appointment cancelled successfully']);
    }

    
    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'new_date' => 'required|date|after:today',
        ]);

        $appointment = Appointment::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        
        $existingAppointment = Appointment::where('appointment_date', $request->new_date)
                                          ->where('status', 'booked')
                                          ->first();
        
        if ($existingAppointment) {
            return response()->json(['error' => 'The selected appointment date is already booked'], 409);
        }

        $appointment->update([
            'appointment_date' => $request->new_date,
            'status' => 'rescheduled',
        ]);

        return response()->json(['message' => 'Appointment rescheduled successfully', 'appointment' => $appointment]);
    }
}
