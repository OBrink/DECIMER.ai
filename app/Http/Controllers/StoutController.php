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

    public function update_smiles_arr(string $smiles, string $mol_block_array){
        // Get updated smiles array based on mol block str from Ketcher windows
        $update_smiles_cmd = 'python3 ../app/Python/get_smiles_from_mol_blocks.py ';
        $updated_smiles = exec($update_smiles_cmd . $mol_block_array);
        // Update those smiles that represent a valid molecule
        $updated_smiles = json_decode($updated_smiles);
        $smiles = json_decode($smiles);
        foreach ($updated_smiles as $key => $smi){
            if ($smi != 'invalid'){
                $smiles[$key] = $smi;
            }
        }
        return json_encode($smiles);
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
        $mol_block_array = $requestData['mol_file_array'];
        $classifier_result_array = $requestData['classifier_result_array'];

        // Get updated smiles array based on mol block str from Ketcher windows
        $smiles_array = $this->update_smiles_arr($smiles_array, $mol_block_array);

        // Check validity of generated SMILES
        $check_validity_command = 'python3 ../app/Python/check_smiles_validity.py ';
        $validity_arr = exec($check_validity_command . $smiles_array);

        // Get list of InchiKeys
        $get_inchikey_command = 'python3 ../app/Python/get_inchikey_list_from_smiles.py ';
        $inchikey_arr = exec($get_inchikey_command . $smiles_array);

        // Avoid timeout
        ini_set('max_execution_time', 300);

        // Send request to STOUT socket to get IUPAC names
        $stout_cmd = 'python3 ../app/Python/stout_predictor_client.py ';
        $iupac_array = exec($stout_cmd . $smiles_array);

        // Write information about how many structures have been processed
        $this->LogStoutProcesses(count(json_decode($iupac_array)));
        
        return back()
            ->with('img_paths', $img_paths)
            ->with('structure_depiction_img_paths', $structure_depiction_img_paths)
            ->with('smiles_array', $smiles_array)
            ->with('validity_array', $validity_arr)
            ->with('iupac_array', $iupac_array)
            ->with('inchikey_array', $inchikey_arr)
            ->with('classifier_result_array', $classifier_result_array)
            ->with('has_segmentation_already_run', $requestData['has_segmentation_already_run'])
            ->with('single_image_upload', $requestData['single_image_upload']);
    }
}
