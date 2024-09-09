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
            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => sprintf('The file %s not exists.', $file->getRealPath()),
            ];
        }
        $command = $this->command . ' ' . $file->getRealPath();
        $process = new Process(['clamdscan', '--fdpass', $file->getRealPath()]);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            // Erreur parce que la commande à executer est invalide ou parce qu'un virus a été détecté ?
            if (!strpos($process->getOutput(), 'SCAN SUMMARY')) {
                $error = new \RuntimeException("Unable to execute command : " . $command);

                return [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $error->getMessage(),
                ];
            }

            // On supprime le fichier si virus détecté + demande explicite de suppression
            if ($removeUnsafe) {
                unlink($file->getRealPath());
            }

            $tabError = explode('n', $process->getOutput());

            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => str_replace(
                    '\\\\n',
                    '',
                    explode('<br />', nl2br(trim($process->getOutput())))
                ),
            ];
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
            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
        } catch (FileScanException $e) {
            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
        }
    }
}
