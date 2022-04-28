<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;

class DecimerSegmentationController extends Controller
{
    public function DecimerSegmentation()
    {
        return view('index');
    }

    public function LogSegmentationProcesses($num_pages, $num_structures){
        $now = new DateTime();
        $now = $now->getTimestamp();
        file_put_contents('decimer_segmentation_log.tsv', $now . "\t" . $num_pages . "\t" . $num_structures . "\n", FILE_APPEND | LOCK_EX);
    }

    public function DecimerSegmentationPost(Request $request)
    {   
        // Get paths of images to process
        $requestData = $request->all();
        $img_paths = $requestData['img_paths'];
        $img_paths = str_replace(' ', '', $img_paths);

        // Avoid timeout
        ini_set('max_execution_time', 300);

		// Run DECIMER Segmentation on images
        $command = 'python3 ../app/Python/decimer_segmentation_client.py ';
        $structure_depiction_img_paths = exec($command . $img_paths);

        // Write data about how many pages and structures have been processed
        $num_pages = count(json_decode($img_paths));
        $num_structures = count(json_decode($structure_depiction_img_paths));
        $this->LogSegmentationProcesses($num_pages, $num_structures);
   
        return back()
            ->with('img_paths', $img_paths)
            ->with('structure_depiction_img_paths', $structure_depiction_img_paths);
    }
}