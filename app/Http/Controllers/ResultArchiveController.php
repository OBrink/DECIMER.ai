<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use ZipArchive;

class ResultArchiveController extends Controller
{
    public function archiveCreation()
    {
        return view('index');
    }

    public function remove_artefacts(array $smiles_array){
        // Get rid of artefacts in SMILES;
        // eg. "["[C-]#[N+][Si][N+]#[C-] |^3:2|"]" --> "["[C-]#[N+][Si][N+]#[C-]"]"
        foreach ($smiles_array as $key => $smiles){
            $smiles_array[$key] = explode(" ", $smiles)[0];
        }
        return $smiles_array;
    }

    public function GenerateZipArchive(){
        // Generate zip archive with timestamp ref in name
        $datetime = new DateTime();
        $timestamp = $datetime->getTimestamp();
        $zip = new ZipArchive();
        $zip_file_path = '../storage/app/public/media/DECIMER_results_' . $timestamp . '.zip';
        if ($zip->open($zip_file_path, ZipArchive::CREATE)!==TRUE) {
            exit("Cannot open <$zip_file_path>\n");
        }
        return [$zip, $zip_file_path];
    }

    public function WriteMolFile(string $file_name, string $smiles){
        // Write molecule that is given as a smiles str into a mol file
        // ($file_name.mol) 
        // Returns path of mol file
        // Untreated brackets lead to 'unexpected token' error
        $smiles = str_replace('(', '\(', $smiles);
        $smiles = str_replace(')', '\)', $smiles);
        $mol_command = 'python3 ../app/Python/generate_mol_file_from_smiles.py ';
        $structure_im_name = basename($file_name);
        exec($mol_command . $smiles . ' '. $structure_im_name);
        $mol_file_path = '../storage/app/public/media/' . $structure_im_name . '.mol';
        return $mol_file_path;
    }

    public function ProcessSmilesJsonArray(string $json_smiles_arr){
        // Read json array with smiles and do curation
        $smiles_array = json_decode($json_smiles_arr);
        $smiles_array = $this->remove_artefacts($smiles_array);
        return $smiles_array;
    }

    public function FillZipFile(
        ZipArchive $zip, 
        string $structure_im_name,
        string $mol_file_path)
        {
        // Put structure depiction and mol file in zip archive
        $structure_img_path = '../storage/app/public/media/' . $structure_im_name;
        $zip->addFile($structure_img_path, $structure_im_name);
        if (file_exists($mol_file_path)){
            $zip->addFile($mol_file_path, $structure_im_name . '.mol');
        }
        return $zip;
    }

    public function archiveCreationPost(Request $request)
    {   
        $request_data = $request->all();
        $img_paths = json_decode($request_data['img_paths']);
        $structure_depiction_img_paths = json_decode($request_data['structure_depiction_img_paths']);
        $smiles_array = $this->ProcessSmilesJsonArray($request_data['smiles_array']);
        
        $iupac_array = $request_data['iupac_array'];

        // Generate zip file
        $zip_info = $this->GenerateZipArchive();
        $zip = $zip_info[0];
        
        // Generate mol files and put them and the images in zip file
        foreach ($smiles_array as $key=>$smiles){
            $structure_im_name = basename($structure_depiction_img_paths[$key]);
            $mol_file_path = $this->WriteMolFile($structure_im_name, $smiles);
            $zip = $this->FillZipFile($zip, $structure_im_name, $mol_file_path);
        }
        // Stringify and return output arrays
        $img_paths = json_encode($img_paths);
        $structure_depiction_img_paths = json_encode($structure_depiction_img_paths);
        $smiles_array = json_encode($smiles_array);

        return back()
           ->with('success_message', 'The file was loaded succesfully.')
            ->with('img_paths', $img_paths)
            ->with('structure_depiction_img_paths', $structure_depiction_img_paths)
            ->with('smiles_array', $smiles_array)
            ->with('download_link', asset('storage/media/' . basename($zip_info[1])))
            ->with('iupac_array', $iupac_array);
    }
}
