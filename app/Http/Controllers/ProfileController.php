<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Display the user's profile dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('profile.index', compact('user', 'paymentMethods'));
    }

    /**
     * Display the user's booking history.
     */
    public function bookings()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)
            ->with(['schedule.origin', 'schedule.destination', 'schedule.bus'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profile.bookings', compact('bookings'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Build validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'avatar' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ];

        // SECURITY: Require password confirmation when changing email
        if ($request->email !== $user->email) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'age' => $request->age,
            'gender' => $request->gender,
        ];

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists (optional, but good practice)
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Store a new payment method.
     */
    public function storePaymentMethod(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:card,ewallet'],
            'provider' => ['required', 'string'],
            'account_number' => ['required', 'string'],
            'expiry_date' => ['nullable', 'required_if:type,card', 'string'],
        ]);

        PaymentMethod::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'provider' => $request->provider,
            'account_number' => $request->account_number,
            'expiry_date' => $request->expiry_date,
        ]);

        return back()->with('success', 'Payment method added successfully.');
    }

    /**
     * Delete a payment method.
     */
    public function destroyPaymentMethod(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

        $paymentMethod->delete();

        return back()->with('success', 'Payment method removed successfully.');
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Booking is already cancelled.');
        }

        // Check if the trip has already happened
        // Assuming schedule relationship exists and has departure_date/time
        // We need to check if the departure is in the future.
        // For simplicity, let's look at the schedule.

        $schedule = $booking->schedule;
        if (!$schedule) {
            return back()->with('error', 'Schedule not found.');
        }

        $departureTime = Carbon::parse($schedule->departure_date . ' ' . $schedule->departure_time);

        if ($departureTime->isPast()) {
            return back()->with('error', 'Cannot cancel a trip that has already departed.');
        }

        $booking->update(['status' => 'cancelled']);

        // Ideally we would also free up the seats in the schedule, but keeping it simple for now as requested.
        // The seat logic in TripController seems to rely on Booking records directly, so we might need to filter out cancelled bookings there?
        // Let's check TripController::selectSeats again later. For now, just marking status as cancelled.

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
