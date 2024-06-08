<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Concour;
use App\Models\Condidature;
use App\Http\Requests\StoreCondidatureRequest;
use App\Http\Requests\UpdateCondidatureRequest;
use Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CondidatureRefuserEmail;
use App\Mail\CondidatureAccepterEmail;
use Illuminate\Http\Request;

class CondidatureController extends Controller
{

    public function sendAccepterEmail(Request $request)
    {
        Condidature::find($request->id)->update(['status'=>'Accepter']);
        $email = $request->email; // Get user data
        Mail::to($email)->send(new CondidatureAccepterEmail());
        return response()->json(["message"=>' Email envoyé avec succès!']);
    }

    public function sendRefuserEmail(Request $request)
    {
        Condidature::find($request->id)->update(['status'=>'Refuser']);
        
        $email = $request->email; // Get user data
        Mail::to($email)->send(new CondidatureRefuserEmail());
        return response()->json(["message"=>'Email envoyé avec succès!']);
    }


        /**
     * Display a listing of the resource.
     */
    public function comiteListConcours()
    {
        $user = Auth::user();
        
        $condidature = Condidature::all()->each(function($item){
            $item->user = User::find($item->user_id);
            $item->concour = Concour::find($item->concour_id);
        });

       $filtredCondidature =  $condidature->filter(function($item) use ($user) {
            return $item->concour->specialite == $user->specialite;
        }); 

        return response()->json($filtredCondidature);
    }

    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        return Condidature::where('concour_id', $id)->get();
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
    public function store(StoreCondidatureRequest $request)
    {
        $data = $request->all(); 

        $condidatureByUser = Condidature::where('user_id', $data['user_id'])
            ->where('concour_id', $data['concour_id'])->get();

        if ($condidatureByUser->count() > 0) {

            return response()->json(['message' => 'Vous avez déjà postulé à ce concours.']);

        }

        $condidatureByConcours = Condidature::where('concour_id', $data['concour_id'])->get();

        $countCondidature = $condidatureByConcours->count();

        $concour = Concour::find($data['concour_id']);

        if ($countCondidature > $concour->nbr_poste) {

            return response()->json(['message' => 'Le nombre de places n\'est pas disponible']);

        }
        
        $cv = $request->file('cv');

        if ($request->hasFile('cv')) {
            $cv = $request->file('cv');
            $cvName = time() . '.' . $cv->getClientOriginalExtension();
            $cv->move(public_path('cv'), $cvName);
            $data['cv'] = $cvName;
        }


        $condidature = Condidature::create($data);

        return response()->json([
            'data' => $condidature,
            'message' => 'Votre candidature a été bien envoyée.'
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Condidature $condidature)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Condidature $condidature)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCondidatureRequest $request, $id)
    {
        $condidature = Condidature::find($id)->update($request->all());
        return response()->json(['message' => 'condidature supprimer avec succès', 'condidature' => $condidature]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $condidature = Condidature::findOrFail($id);
        $condidature->delete();
        return response()->json(['message' => 'condidature supprimer avec succès']);
    }


   
}
