<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use phpDocumentor\Reflection\PseudoTypes\True_;

class DecimerSegmentationController extends Controller
{
    public function DecimerSegmentation()
    {
        return view('index');
    }

    public function LogSegmentationProcesses(int $num_pages, int $num_structures){
        $now = new DateTime();
        $now = $now->getTimestamp();
        file_put_contents('decimer_segmentation_log.tsv', $now . "\t" . $num_pages . "\t" . $num_structures . "\n", FILE_APPEND | LOCK_EX);
    }

    public function SegmentChemicalStructures(array $img_paths) {
        // Given an array of input image paths, this function returns an array of 
        // pathes of chemical structures that have been segmented in the input images
        $command = 'python3 ../app/Python/decimer_segmentation_client.py ';
        $structure_depiction_img_paths = exec($command . json_encode($img_paths));
        return json_decode($structure_depiction_img_paths);
    }

    public function DecimerSegmentationPost(Request $request)
    {   
        // Get paths of images to process
        $requestData = $request->all();
        $img_paths = $requestData['img_paths'];
        $img_paths = str_replace(' ', '', $img_paths);
        $img_paths = json_decode($img_paths);

        // Avoid timeout
        ini_set('max_execution_time', 300);

		// Run DECIMER Segmentation on images
        $structure_depiction_img_paths = $this->SegmentChemicalStructures($img_paths);

        // Write data about how many pages and structures have been processed
        $num_pages = count($img_paths);
        $num_structures = count($structure_depiction_img_paths);
        $this->LogSegmentationProcesses($num_pages, $num_structures);
   
        return back()
            ->with('img_paths', json_encode($img_paths))
            ->with('structure_depiction_img_paths', json_encode($structure_depiction_img_paths))
            ->with('has_segmentation_already_run', "true")
            ->with('single_image_upload', $requestData['single_image_upload']);
    }
}