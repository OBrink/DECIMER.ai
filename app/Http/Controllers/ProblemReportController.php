<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResultArchiveController;
use Illuminate\Http\Request;
use DateTime;

class ProblemReportController extends Controller
{
    public function ProblemReport()
    {}

    public function copy_file_to_problem_report_storage(string $path){
        // Takes file path and moves the corresponding file to a separate directory
        // to save it from the temporary file deletion routine
        $media_dir = '../storage/app/public/media/';
        $orig_path = $media_dir . basename($path);
        $problem_report_storage_dir = '../storage/app/public/reported_results/';
        $dest_path = $problem_report_storage_dir . basename($path);
        copy($orig_path, $dest_path);
    }

    public function ProblemReportPost(Request $request)
    {        
        // Use timestamp as ID
        $now = new DateTime();
        $now = $now->getTimestamp();
        // Move the processed files to a different location
        $requestData = $request->all();
        $smiles = explode(" ", $requestData['smiles'])[0];
        $structure_depiction_img_path = $requestData['structure_depiction_img_path'];

        // Write mol file based on given smiles representation of molecule
        $archive_controller = new ResultArchiveController;
        $mol_file_path = $archive_controller->WriteMolFile($structure_depiction_img_path, $smiles);

        // Copy mol file and structure image into
        $this->copy_file_to_problem_report_storage($mol_file_path, $now);
        $this->copy_file_to_problem_report_storage($structure_depiction_img_path, $now);
    }
}
