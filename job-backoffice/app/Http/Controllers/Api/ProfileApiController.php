<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['company', 'school']);

        return response()->json($user);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            // Phone is freeform; we just sanity-check the shape. Null is
            // allowed so users can clear it from their profile.
            'phone' => 'sometimes|nullable|string|min:6|max:32|regex:/^[0-9+\-\s()]+$/',
        ], [
            'phone.regex' => 'Le numéro de téléphone contient des caractères invalides.',
        ]);

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
        }

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    public function uploadProfilePicture(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('photo')->store('profile-pictures', 'public');
        $request->user()->update(['profile_picture' => $path]);

        return response()->json(['profile_picture' => $path]);
    }

    public function myResumes(Request $request): JsonResponse
    {
        $resumes = Resume::where('userId', $request->user()->id)
            ->whereNull('deleted_at')
            ->latest()
            ->get();

        return response()->json($resumes);
    }

    public function uploadResume(Request $request): JsonResponse
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:5120',
        ]);

        $user = $request->user();
        $file = $request->file('resume');
        $path = $file->store('resumes', 'public');

        $resume = Resume::create([
            'filename'       => $file->getClientOriginalName(),
            'fileUri'        => $path,
            'userId'         => $user->id,
            'contactDetails' => ['name' => $user->name, 'email' => $user->email],
            'summary'        => '',
            'skills'         => '',
            'experience'     => '',
            'education'      => '',
        ]);

        // Update user cv_url to latest resume
        $user->update(['cv_url' => $path]);

        return response()->json($resume, 201);
    }

    public function deleteResume(Request $request, string $id): JsonResponse
    {
        $resume = Resume::where('userId', $request->user()->id)->findOrFail($id);
        $resume->delete();

        return response()->json(['message' => 'CV supprimé.']);
    }
}
