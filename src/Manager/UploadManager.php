<?php

namespace UploadBundle\Manager;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UploadManager
{
    /**
     * @return JsonResponse|void
     */
    public function checkTemporaryDirectory(string $folderName = '')
    {
        // check temporary folder
        if (!file_exists($folderName)) {
            if (!mkdir($concurrentDirectory = $folderName, 0775) && !is_dir($concurrentDirectory)) {
                $error = new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));

                return $this->JsonResponse($error, true);
            }
        }

        return [];
    }

    /**
     * @return JsonResponse|void
     */
    public function checkFileName(array $data = [])
    {
        if (!isset($data['fileName'])) {
            $error = new \RuntimeException("parameter 'fileName' was not found");

            return $this->JsonResponse($error, true);
        }

        return [];
    }

    /**
     * @return void
     */
    public function checkFile(string $fileName = '', string $folder = '', string $extension = '')
    {
        if (!empty($fileName) && !empty($extension)) {
            $folder = sprintf('%s/%s', $folder, $extension);
        } elseif (!empty($fileName) && empty($extension)) {
            $tabFileName = explode('.', $fileName);
            $nb = count($tabFileName);
            $folder = sprintf('%s/%s', $folder, $tabFileName[$nb - 1]);
        }

        if (!file_exists(sprintf('%s/%s', $folder, $fileName)) || empty($fileName)) {
            $error = new \RuntimeException(
                sprintf("File '%s' was not found", $fileName)
            );

            return $this->JsonResponse($error, true);
        }

        return new UploadedFile(sprintf('%s/%s', $folder, $fileName), $fileName, null, null, true);
    }

    public function populateMedata(array &$data, File $file): array
    {
        /*
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

    public function isPdf(File $file): bool|File
    {
        if ('pdf' !== $file->getExtension()) {
        }

        return true;
    }

    public function scanVirus(File $file): bool|array
    {
        return false;
    }

    /**
     * @return array|Response
     */
    private function JsonResponse($error, bool $asArray = false)
    {
        if ($asArray) {
            return [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $error->getMessage(),
            ];
        }

        return new Response(
            json_encode([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $error->getMessage(),
            ]),
            Response::HTTP_BAD_REQUEST,
            ['content-type' => 'application/json']
        );
    }
}
