<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Concour; 
use App\Models\Condidature;
use Illuminate\Validation\ValidationException;
use Auth;

class ConcoursController extends Controller
{ 

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return Concour::with('comments.user')->get();

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'contenu' => 'required|string',
            'titre' => 'required|string',
            'image' => 'required',
            'specialite' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
            'nbr_poste' => 'required'
        ]);

        // Vérifier si un concours avec les mêmes données existe déjà
        $existingConcours = Concour::where('contenu', $validatedData['contenu'])
            ->where('titre', $validatedData['titre'])
            ->where('specialite', $validatedData['specialite'])
            ->where('date_debut', $validatedData['date_debut'])
            ->where('date_fin', $validatedData['date_fin'])
            ->where('nbr_poste', $validatedData['nbr_poste'])
            ->exists();

        if ($existingConcours) {
            return response()->json(['message' => 'Le concours existe déjà']);
        }

        $image = $request->file('image');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName;
        }

        $concour = Concour::create($validatedData);

        return response()->json(['concour' => $concour, 'message' => 'concours ajouté avec succès']);
    }


    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Concour::with('comments.user')->findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $concours = Concour::findOrFail($id);

        $validatedData = $request->all();

        
        $image = $request->file('image');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName;
        }


        $concours->update($validatedData);

        return response()->json(['concour' => Concour::findOrFail($id),'test'=>$image, 'message' => 'Concours modifié avec succès'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $concours = Concour::findOrFail($id);
        $concours->delete();
        return response()->json(['message' => 'concours supprimer avec succès']);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function condidatureByUser()
    {

        $user = Auth::user();

        $concour_id = Condidature::where('user_id', $user->id)->get()->pluck('status', 'concour_id');

        return Concour::whereIn('id', array_keys($concour_id->toArray()))->get()->map(function ($item) use ($concour_id) {

            if (isset ($concour_id[$item->id])) {
                $item->status = $concour_id[$item->id];
            }
            return $item;
        });
    }

}
