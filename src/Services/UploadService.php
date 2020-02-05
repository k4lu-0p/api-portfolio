<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\File;

class UploadService {

    /**
     * @param string $file_base64 - Fichier encodé en base64.
     * @param string $file_name - Nom de fichier uploadé.
     * @param string $folder - Dossier de destination pour les fichiers uploadés.
     * @param string $file_ext - Extension du fichier uploadé.
     */
    public static function handle($file_base64,  $file_name,  $folder,  $file_ext) {
        $decodedFile = base64_decode($file_base64); // Decode le fichier.
        $tmpPath = sys_get_temp_dir() . '/' . $file_name . uniqid() . "." . $file_ext; // Chemin temporaire.
        file_put_contents($tmpPath, $decodedFile); // Créer le fichier temporaire.
        $uploadedFile = new File($tmpPath);
        $uploadedFile->move($folder, $tmpPath);
        return $uploadedFile->getFilename();
    }


}