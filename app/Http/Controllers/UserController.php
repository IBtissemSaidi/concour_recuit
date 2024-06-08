<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Auth;
class UserController extends Controller
{
   public function index()
  {
    $user = Auth::user();

    if($user->role == 'admin')
    {
      $users = User::where('role','condidate')->orWhere('role','Gestionnaire')->get();
    }

    if($user->role == 'Gestionnaire')
    {
      $users = User::where('role','condidate')->orWhere('role','comite')->get();
    }

    return $users;
  }

 




  public function update(Request $request, $id)
{ 

    $validatedData = $request->validate([
        'name' => 'required|string|max:255', 
        'phone' => 'required|string|min:8',
        'specialite' => 'nullable|string|max:255'
    ]);
 
    if (isset($request->email)) {

      $validatedData['email'] = $request->email;
  }

    if (isset($request->profilePicture)) {

        $validatedData['profilePicture'] = $request->profilePicture;
    }


    if (isset($request->password) && !empty($request->password)) {
        $validatedData['password'] = Hash::make($request->password);
    }

    // Mettre à jour l'utilisateur dans la base de données
    User::find($id)->update($validatedData);

    // Récupérer à nouveau l'utilisateur mis à jour
    $updatedUser = User::find($id);

    return response()->json(['user' => $updatedUser, 'message' => 'Utilisateur modifié avec succès'], 200);
}


  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    $user = User::findOrFail($id);
    $user->delete();
    return response()->json(['message' => 'Utlisateur supprimé avec succés'], 200);
  }


/** */



    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }
  


}