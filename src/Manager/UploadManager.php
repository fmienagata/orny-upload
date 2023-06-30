<?php

namespace UploadBundle\Manager;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class UploadManager
{
    /**
     * @return JsonResponse|void
     */
    public function checkTemporaryDirectory(string $folderName = '')
    {
        //check temporary folder
        if (!file_exists($folderName)) {
            if (!mkdir($concurrentDirectory = $folderName, 0775) && !is_dir($concurrentDirectory)) {
                $error = new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));

                return $this->JsonResponse($error);
            }
        }

        return [];
    }

    /**
     * @param array $data
     *
     * @return JsonResponse|void
     */
    public function checkFileName(array $data = [])
    {
        if (!isset($data['fileName'])) {
            $error = new \RuntimeException("parameter 'fileName' was not found");

            return $this->JsonResponse($error);
        }

        return [];
    }

    /**
     * @param string $fileName
     * @param string $folder
     *
     * @return void
     */
    public function checkFile(string $fileName = '', string $folder = '', string $extension = '')
    {
        if (!empty($fileName) && !empty($extension)) {
            $folder = sprintf('%s/%s', $folder, $extension);
        } elseif (!empty($fileName) && empty($extension)) {
            $tabFileName = explode('.', $fileName);
            $nb = count($tabFileName);
            $folder = sprintf('%s/%s', $folder, $tabFileName[$nb-1]);
        }
        
        if (!file_exists(sprintf('%s/%s', $folder, $fileName)) || empty($fileName)) {
            $error = new \RuntimeException(
                sprintf("File '%s' was not found", $fileName)
            );

            return $this->JsonResponse($error);
        }

        return new UploadedFile(sprintf('%s/%s', $folder, $fileName), $fileName, null, null, true);
    }

    /**
     * @param array $data
     * @param File  $file
     *
     * @return array
     */
    public function populateMedata(array &$data, File $file) :array
    {
        /**
         * aTime: 2023-03-24 11:58:24
         * mTime: 2023-03-24 11:58:24
         * cTime: 2023-03-24 11:58:24
         * inode: 289502
         * size: 334109
         * perms: 0100775
         */
        $data['metadata']['aTime'] = $file->getATime();
        $data['metadata']['mTime'] = $file->getMTime();
        $data['metadata']['cTime'] = $file->getCTime();
        $data['metadata']['inode'] = $file->getInode();
        $data['metadata']['size'] = $file->getSize();
        $data['metadata']['perms'] = $file->getPerms();

        return $data;
    }

    /**
     * @param File $file
     *
     * @return bool|File
     */
    public function isPdf(File $file) : bool|File
    {
        if ($file->getExtension() !== 'pdf') {

        }

        return true;
    }

    /**
     * @param File $file
     *
     * @return bool|array
     */
    public function scanVirus(File $file) : bool|array
    {
        return false;
    }

    private function JsonResponse($error)
    {
        $response = new Response(
            json_encode([
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ]),
            Response::HTTP_BAD_REQUEST,
            ['content-type' => 'application/json']
        );

        return $response;
    }
}
