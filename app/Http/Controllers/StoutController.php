<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;

class StoutController extends Controller
{
    public function Stout()
    {
        return view('index');
    }

    public function remove_artefacts(string $json_smiles_arr){
        // Get rid of artefacts in SMILES;
        // eg. "["[C-]#[N+][Si][N+]#[C-] |^3:2|"]" --> "["[C-]#[N+][Si][N+]#[C-]"]"
        $smiles_array = json_decode($json_smiles_arr);
        foreach ($smiles_array as $key => $smiles){
            $smiles_array[$key] = explode(" ", $smiles)[0];
        }
        $json_smiles_arr = json_encode($smiles_array);
        return $json_smiles_arr;
    }

    public function LogStoutProcesses($num_structures){
        // Write data about how many structures have been processed
        $now = new DateTime();
        $now = $now->getTimestamp();
        
        if ($num_structures > 20){
            $num_structures = 20;
        }
        file_put_contents('stout_log.tsv', $now . "\t" . $num_structures . "\n", FILE_APPEND | LOCK_EX);
    }

    public function StoutPost(Request $request)
    {
        // Get all data that needs to be processed or sent back
        $requestData = $request->all();
        $img_paths = $requestData['img_paths'];
        $structure_depiction_img_paths = $requestData['structure_depiction_img_paths'];
        $smiles_array = $requestData['smiles_array'];

        // Remove artefacts
        $smiles_array = $this->remove_artefacts($smiles_array);

        // Avoid timeout
        ini_set('max_execution_time', 300);

        // Send request to local STOUT server to get IUPAC names
        $stout_command = 'python3 ../app/Python/stout_predictor_client.py ';
        $iupac_array = exec($stout_command . $smiles_array);

        // Write information about how many structures have been processed
        $this->LogStoutProcesses(count(json_decode($iupac_array)));
        
        return back()
            ->with('img_paths', $img_paths)
            ->with('structure_depiction_img_paths', $structure_depiction_img_paths)
            ->with('smiles_array', $smiles_array)
            ->with('iupac_array', $iupac_array);
    }
}
