<?php
// src/Service/FileUploadService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService 
{
    // Attention au nom exact de la variable ici
    public function __construct(private string $uploadDirectory) {}

    // FIX : Ajout de "function" et respect des minuscules/majuscules pour $subdir
    public function upload(UploadedFile $file, string $subdir, ?string $filename = null): string 
    {
        $ext      = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $filename = ($filename ?? uniqid()) . '.' . $ext;

        // FIX : $this->uploadDirectory (sans s) et $subdir (tout en minuscules)
        $file->move($this->uploadDirectory . '/' . $subdir, $filename);

        return $subdir . '/' . $filename;
    }
}