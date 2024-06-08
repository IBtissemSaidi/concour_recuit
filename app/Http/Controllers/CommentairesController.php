<?php

namespace App\Http\Controllers;

use App\Models\Commentaire;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentairesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer tous les commentaires
       return Commentaire::all();
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Afficher le formulaire pour créer un nouveau commentaire
        return view('commentaires.create');
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
        $commentaire = Commentaire::findOrFail($id);

        return response()->json($commentaire);
    }





    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
     
        // Valider les données du formulaire
        $request->validate([
            'contenu' => 'required',
            'concour_id' => 'required',
            'user_id' => 'required' ,
            'reactions' => 'required' ,
             'nombreDeReactions' => 'required' ,
        ]);
    
        // Créer un nouveau commentaire
      $comment =   Commentaire::create($request->all());

        
      $commentWithUser =  Commentaire::with('user')->find($comment->id);
    
        

        return response()->json(["message"=>"Commentaire créé avec succès.","data"=>$commentWithUser]); 

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $commentaires = Commentaire::findOrFail($id);
        $commentaires->update($request->all());
        return response()->json(['commentaire' => $commentaires, 'message' => 'Commentaire modifié avec succès'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $commentaires = Commentaire::findOrFail($id);
        $commentaires->delete();
        return response()->json(['message' => 'commentaire supprimer avec succès']);
    }




    /** */

    public function getConcoursComments($concour_id)
{
    
        try {
            $comments = Commentaire::where('concour_id', $concour_id)->get();
            return response()->json($comments, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
}

/** likes */
public function likeComment(Request $request, $commentId)
{
    try {

        $comment = Commentaire::find($commentId);
        
        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        // $userIndex = array_search(auth()->user()->id, $comment->reactions);

        // if ($userIndex === false) {
            $comment->nombreDeReactions += 1;
            // $comment->reactions[] = auth()->user()->id;
        // } else {
            // $comment->nombreDeReactions -= 1;
            // unset($comment->reactions[$userIndex]);
        // }

        $comment->save();

        return response()->json(['message' => 'commentaire supprimer avec succès','data'=>Commentaire::with('user')->find($commentId)], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
