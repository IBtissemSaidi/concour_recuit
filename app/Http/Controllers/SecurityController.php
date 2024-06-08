<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Auth;

class SecurityController extends Controller
{
   /* public function login(Request $request) {

        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('MyAppToken');
            return response()->json(['token' => $token->plainTextToken,'user'=> $user,'message'=>'Bienvenue chez nous'], 200);
        } else {
            return response()->json(['message' => 'Vous n êtes pas inscrit !'], 401);
        }
        
    }*/

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Veuillez remplir tous les champs.'], 422);
        }
    
        $credentials = $request->only('email', 'password');
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'Adresse email non enregistrée.'], 401);
        }
    
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 401);
        }
    
        $user = Auth::user();
        $token = $user->createToken('MyAppToken');
    
        return response()->json(['token' => $token->plainTextToken, 'user' => $user, 'message' => 'Bienvenue chez nous'], 200);
    }
    


    public function register(Request $request) {

        $role = $request->role ? $request->role : 'condidate';
        $specialite = $request->specialite ? $request->specialite : null;

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6', 
            'phone' => 'required|string|min:8', 
        ],

    [
            'name.required' => 'Le nom est requis',
            'email.required' => 'L\'adresse email est requise',
            'email.email' => 'L\'adresse email n\'est pas valide',
            'email.unique' => 'L\'adresse email doit être unique',
            'password.required' => 'Le mot de passe est requis',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères',
            'phone.required' => 'Le numéro de téléphone est requis',
            'phone.min' => 'Le numéro de téléphone doit contenir au moins 8 caractères',
        ]
    
    );
    
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'role' => $role,
            'specialite' => $specialite,
            'password' => Hash::make($validatedData['password']),
        ]);
    
        $token = $user->createToken('MyAppToken')->accessToken;
        return response()->json(['user'=>$user,'token' => $token,'message'=>'utilisateur créé avec succés'], 201);
    }





    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            "message" =>   "logged out"
        ]);
    }




    public function google(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'googlePhotoUrl' => 'required|url',
            'phone' => 'string|min:8|nullable',
        ]);
        $name = $data['name'];
        $email = $data['email'];
        $googlePhotoUrl = $data['googlePhotoUrl'];

        try {
            // Check if user already exists
            $user = User::where('email', $email)->first();

            if ($user) {
                $token = Auth::login($user);

                return response()->json([
                    'user' => $user,
                    'token' => $token,
                ], 200);
            } else {
                // Generate a random password
                $generatedPassword = Str::random(16);

                // Create new user
                $user = new User();
                $user->name = strtolower(str_replace(' ', '', $name)) . Str::random(4);
                $user->email = $email;
                $user->password = Hash::make($generatedPassword);
                $user->profilePicture = $googlePhotoUrl;
                $user->phone = '';
                $user->role = 'condidate';
                $user->save();

                // Log in the new user
                $token = Auth::login($user);

                return response()->json([
                    'user' => $user,
                    'token' => $token,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
