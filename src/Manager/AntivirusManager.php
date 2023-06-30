<?php

namespace UploadBundle\Manager;

use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Exception\SocketException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class AntivirusManager
{
    /**
     * @param Scanner $scanner
     * @param string  $command
     */
    public function __construct(
        private Scanner $scanner,
        private string $command = ''
    ) {}

    /**
     * Check the file integrity
     *
     * @param string $filePath      Chemin du fichier à contrôler
     * @param bool   $removeUnsafe  Supprimer automatiquement le fichier si celui-ci n'est pas sûr
     * @return boolean
     */
    public function isFileSafe(File $file, $removeUnsafe = false)
    {
        if (!file_exists($file->getRealPath())) {
            return new Response(
                json_encode([
                    'code' => 0,
                    'message' => sprintf('The file %s not exists.', $file->getRealPath()),
                ]),
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }
        $command = $this->command . ' ' . $file->getRealPath();
        $process = new Process(['clamdscan', '--fdpass', $file->getRealPath()]);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            // Erreur parce que la commande à executer est invalide ou parce qu'un virus a été détecté ?
            if (!strpos($process->getOutput(), 'SCAN SUMMARY')) {
                throw new \RuntimeException("Unable to execute command : " . $command);
            }

            // On supprime le fichier si virus détecté + demande explicite de suppression
            if ($removeUnsafe) {
                unlink($file->getRealPath());
            }

            $tabError = explode('n', $process->getOutput());

            return new Response(
                json_encode([
                    'code' => $process->getExitCode(),
                    'message' => str_replace('\\n', '', explode('<br />', nl2br(trim($process->getOutput())))),
                ]),
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }

        return $process->isSuccessful();
    }

    /**
     * @param File $file
     * @param      $removeUnsafe
     *
     * @return array|Response|void
     */
    public function scanner(File $file, $removeUnsafe = false)
    {
        try {
            $scannedFile = $this->scanner->scan($file);
            if (!$scannedFile->isClean()) {
                return [$scannedFile->getVirusName()];
            }
        } catch (SocketException $e) {
            return new Response(
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        } catch (FileScanException $e) {
            return new Response(
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }
    }
}
