<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file)
    {
       $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
       $safeFilename = $this->slugger->slug($originalFileName);
       $filename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
       try {
        $file->move($this->getTargetDirectory(), $filename);
       } catch (FileException $e) {
        
       }

       return $filename;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}