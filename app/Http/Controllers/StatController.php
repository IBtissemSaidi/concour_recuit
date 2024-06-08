<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Commentaire;
use App\Models\Concour;
use App\Models\Condidature;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatController extends Controller
{
    public function stat()
    {

        //nbr comment 
        $commentaires = Commentaire::all()->count();
        //nbr compte
        $users = User::all()->count();

        //nbr concour
        $concours = Concour::all()->count();

        //nbr condidature 
        $condidatures = Condidature::all()->count();

        $userByRoleFormat = [];

        User::all()->groupBy('role')->each(function ($index, $item) use (&$userByRoleFormat) {

            $userByRoleFormat[] = ["label" => $item, "value" => $index->count()];

        }); 

        $candidaturesParMoisEtStatus = [];

         Condidature::all()->groupBy('status')->each(function ($item, $index) use (&$candidaturesParMoisEtStatus) {

            $candidaturesParMoisEtStatus[] = [
                "name" => $index,
                "type" => 'area',
                "fill" => 'gradient',
                "data" => array_values($item->groupBy(function ($val) {
                    return Carbon::parse($val->created_at)->format('Y-m');
                })->map(function ($items, $month) {
                    // Count the number of items for each month
                    return $items->count();
                })->toArray()),

            ]; 

        });

        $nombreConcoursParMois = [];

        Concour::all()->groupBy(function ($val) { 
            return Carbon::parse($val->created_at)->format('Y-m');
        })->each(function($item,$index) use (&$nombreConcoursParMois){
            $nombreConcoursParMois[] = [
                'label'=> $index,'value'=>$item->count()
            ];
        });

        return response()->json([
            "commentaires" => $commentaires,
            "users" => $users,
            "concours" => $concours,
            "condidatures" => $condidatures,
            'userByRole' => $userByRoleFormat,
            'nombreConcoursParMois' => $nombreConcoursParMois,
            'candidaturesParMoisEtStatus' => $candidaturesParMoisEtStatus
        ]);

    }
}
