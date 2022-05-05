<?php

namespace App\Http\Controllers;

use App\Http\Controllers\StoutController;
use Illuminate\Http\Request;
use DateTime;
use ZipArchive;

class ResultArchiveController extends Controller
{
    public function archiveCreation()
    {
        return view('index');
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

    public function WriteMolFile(string $file_name, string $mol_block){
        // Write molecule that is given as a smiles str into a mol file
        // ($file_name.mol) 
        $structure_im_name = basename($file_name);
        $mol_file_path = '../storage/app/public/media/' . $structure_im_name . '.mol';
        $mol_file = fopen($mol_file_path, "w");
        fwrite($mol_file, $mol_block);
        fclose($mol_file);
        return $mol_file_path;
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
        $img_paths = $request_data['img_paths'];
        $structure_depiction_img_paths = json_decode($request_data['structure_depiction_img_paths']);
        $smiles_array = $request_data['smiles_array'];
        $iupac_array = $request_data['iupac_array'];
        $mol_block_array = $request_data['mol_file_array'];

        // Get updated smiles array based on mol block str from Ketcher windows
        $stout_controller = new StoutController();
        $smiles_array = $stout_controller->update_smiles_arr($smiles_array, $mol_block_array);
        
        // Check validity of generated SMILES
        $check_validity_command = 'python3 ../app/Python/check_smiles_validity.py ';
        $validity_arr = exec($check_validity_command . $smiles_array);

        // Generate zip file
        $zip_info = $this->GenerateZipArchive();
        $zip = $zip_info[0];

        $mol_block_array = json_decode($mol_block_array);
        // Generate mol files and put them and the images in zip file
        foreach ($mol_block_array as $key=>$mol_block){
            $structure_im_name = basename($structure_depiction_img_paths[$key]);
            $mol_file_path = $this->WriteMolFile($structure_im_name, $mol_block);
            $zip = $this->FillZipFile($zip, $structure_im_name, $mol_file_path);
        }
        
        // Stringify and return output arrays
        $structure_depiction_img_paths = json_encode($structure_depiction_img_paths);
        return back()
           ->with('success_message', 'The file was loaded succesfully.')
            ->with('img_paths', $img_paths)
            ->with('structure_depiction_img_paths', $structure_depiction_img_paths)
            ->with('smiles_array', $smiles_array)
            ->with('validity_array', $validity_arr)
            ->with('download_link', asset('storage/media/' . basename($zip_info[1])))
            ->with('iupac_array', $iupac_array);
    }
}
