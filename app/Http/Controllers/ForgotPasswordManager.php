<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\WelcomeEmail;

class ForgotPasswordManager extends Controller
{
    //
    function forgetPassword(){
       return view("forget-password"); 
    }

    function forgetPasswordPost(Request $request){

        $request->validate([
            'email' => "required|email|exists:users",
        ]);

        $user = User::where('email',$request->email)->first();

        $token = Str::random(64);

       $check=  DB::table('password_reset_tokens')->where('email', $request->email)->get();
        
       if($check->count()>0)
       {
        DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->update([
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
       }else{
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
       }

      

        

        Mail::to($user->email)->send(new WelcomeEmail($user,$token));

        return response()->json(['message' => __("Nous avons envoyé un email pour réinitialiser le mot de passe.")]);

        
    }

    function resetPassword($token){
     return view("new-password", compact('token'));
    }

    function resetPasswordPost(Request $request){
         $request->validate([
               "email" => "required|email|exists:users",
               "password" => "required|string|min:6|confirmed",
               "password_confirmation" => "required"
         ]);

         $updatePassword = DB::table('password_reset_tokens')->where([
            "email" =>$request->email,
            "token" => $request->token

         ])->first();

         if (!$updatePassword){ 
             return response()->json(['message' => __('Invalid'),  "email" =>$request->email,
             "token" => $request->token]);

         }

         User::where("email", $request->email)->update(["password" => Hash::make($request->password)]);
        
         DB::table("password_reset_tokens")->where(["email" => $request->email])->delete();

       

         return response()->json(['message' => __('Réinitialisation du mot de passe réussie')]);

    }  


    public function sendVerificationEmail(Request $request)
    { 
 
        $user = User::where('email', $request->email)->first(); 
      
            
        if (!$user) {
            return response()->json(['message' => __('Utilisateur introuvable')], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('L\'e-mail a déjà été vérifié')], 400);
        }

            
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'E-mail de vérification envoyé']);
    }


    public function verify(Request $request)
    {

        if (!$request->hasValidSignature()) {
            return response()->json(['message' => __('Lien de vérification non valide ou expiré')], 400);
        }

        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            
            return response()->json(['message' => __('L\'e-mail a déjà été vérifié')], 400);
        } 

            
        $user->markEmailAsVerified();

        event(new Verified($user));

        return response()->json(['message' => __('E-mail vérifié avec succès')]);
    }


    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            "oldPassword" => "min:6|required",
            "newPassword" => "min:6|required",
        ]);

        if (Hash::check($data['oldPassword'], Hash::make($data['newPassword']))) {
            return response()->json([
                'response' => false,
                'message' => __("New password can't be the same as the current one")
            ], 400);
        }

        if (!Hash::check($data['oldPassword'], Auth::user()->password)) {
            return response()->json([
                'response' => false,
                'message' => __("Invalid Password")
            ], 400);
        }

        User::find(Auth::user()->id)->update(['password' => Hash::make($data['newPassword'])]);

        return response()->json([
            'response' => true,
            "message" => __("Password updated successfully")
        ], 200);
    }

}
