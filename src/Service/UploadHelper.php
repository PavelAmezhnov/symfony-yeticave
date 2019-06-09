<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploadHelper
{
    const ROOT_UPLOAD_DIR = '/uploads';
    const DEFAULT_DIR = self::ROOT_UPLOAD_DIR . '/tmp';
    const AVATAR = self::ROOT_UPLOAD_DIR . '/avatar';
    const LOT = self::ROOT_UPLOAD_DIR . '/lot';
    
    private $params;
    
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
    
    public function saveUpload(UploadedFile $uploadedFile, $where = self::DEFAULT_DIR): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $destination = $this->params->get('kernel.project_dir') . '/public' . $where;
        $uploadedFile->move($destination, $newFilename);
        
        return $newFilename;
    }
}