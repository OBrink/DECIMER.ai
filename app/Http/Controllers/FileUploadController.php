<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;

class FileUploadController extends Controller
{
    public function fileUpload()
    {
        return view('index');
    }

    public function oldFileRemoval()
    {
        // Remove files from public directory that have not been accessed 
        // in the last hour
        $dir = '../storage/app/public/media/*';
        foreach(glob($dir) as $file)  {
            $last_accessed = fileatime($file);
            $now = new DateTime();
            $now = $now->getTimestamp();
            if($now - $last_accessed > 3600){
                unlink($file);
            }
        }
    }

    public function cleanFilename(string $file_name)
    {
        // Remove characters that might cause problems from uploaded file name
        $forbidden_chars = array("!","#","$","%","&","(",")","*","+",
                                 ",","-","/",":",";","<","=",">",
                                 "?","@","[","\\","]","^","`","{","|",
                                 "}","~","\t","\n", " ");
        foreach ($forbidden_chars as $forbidden_char){
            $file_name = str_replace($forbidden_char, '_', $file_name);
        }
        return $file_name;
    }

    public function fileUploadPost(Request $request)
    {
        $this->oldFileRemoval();
        // It is annoying but we need to do some manual input validation as we accept
        // multiple input files
        $errors = array();
        $processed_pdf = false;
        $processed_images = false;

        $structure_depiction_img_paths = array();
        $valid_file_endings = array('pdf', 'jpg', 'peg', 'png', 'ebp', 'eic', 'eif');

        $files = $request->file('file');
        foreach ($files as $file) {
            // Get file names, remove forbidden characters, save files
            $file_name = $file->getClientOriginalName();
            $file_name = $this->cleanFilename($file_name);
            $file_path = $file->storeAs('public/media', $file_name);
            $file_ending = strtolower(substr($file_name, -3));

            if ($file_ending == 'pdf') {
                $img_paths = exec('python3 ../app/Python/convert_pdf_to_images.py ' . $file_path);
                $structure_depiction_img_paths = null;
                $processed_pdf = true;
                
            } elseif (in_array($file_ending, $valid_file_endings)) {
                $img_paths = '[]';
                // Convert RGBA to RGB image
                exec('python3 ../app/Python/normalise_img_format.py ' . 'storage/media/' . $file_name);
                array_push($structure_depiction_img_paths, 'storage/media/' . $file_name);
                $processed_images = true;

            } else {
                array_push($errors, 'Invalid file! Valid formats: pdf, png, jpg/jpeg, webp, HEIC');
            }
        };
        // If it's not null, encode structure depiction paths
        if ($structure_depiction_img_paths) {
            $structure_depiction_img_paths = json_encode($structure_depiction_img_paths);
        };

        // Validation: Only accept inputs if pdf document OR images have been uploaded.
        if ($processed_images) {
            if ($processed_pdf) {
                array_push($errors, 'Invalid mixed inputs! Please upload a pdf document or upload chemical structure images.');
            }
        }
        
        // If there are errors, return them
        if(count($errors) > 0) {
            return back()
                ->with('errors', $errors);
        }

        return back()
            ->with('success_message', 'The file was loaded succesfully.')
            ->with('file_name', $file_name)
            ->with('img_paths', $img_paths)
            ->with('structure_depiction_img_paths', $structure_depiction_img_paths);
    }
}
