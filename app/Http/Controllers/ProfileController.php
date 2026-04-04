<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function show(Request $request): View
    {
        $user = $request->user();

        $customersServed = \App\Models\Customer::count();
        $servicesCompleted = \App\Models\ServiceReport::where('status', 'Completed')->count();
        $monthsActive = (int)$user->created_at->diffInMonths(\Carbon\Carbon::now());
        $averageRating = 4.8;
        $recentActivity = \App\Models\Transaction::with('report.customer')
            ->latest()
            ->limit(5)
            ->get();

        return view('profile.show', compact(
            'user', 'customersServed', 'servicesCompleted',
            'monthsActive', 'averageRating', 'recentActivity'
        ));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle profile picture upload to local storage
        if ($request->hasFile('profile_picture')) {
            try {
                if ($user->profile_picture) {
                    $oldPath = str_replace(url('/storage') . '/', '', $user->profile_picture);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                
                $validated['profile_picture'] = url('/storage/' . $path);
                $validated['profile_picture_public_id'] = null;
            } catch (\Exception $e) {
                return Redirect::route('profile.edit')
                    ->withErrors(['profile_picture' => 'Failed to upload profile picture. Please try again.']);
            }
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->profile_picture) {
            $oldPath = str_replace(url('/storage') . '/', '', $user->profile_picture);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Persist "who deleted" before logout; bypass $fillable.
        $user->forceFill(['deleted_by' => $user->id])->save();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
