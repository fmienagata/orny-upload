<?php

namespace UploadBundle\Tests\Helper;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Api extends \Codeception\Module
{
    use ToolsTrait;

    public function _initialize()
    {
        set_time_limit(-1);
        $rootDir = __DIR__ . '/../../../';

        // cleaning elasticsearch
        $cwds = [
            $_ENV['PHP_BIN'] . ' -d memory_limit=-1 ' . $rootDir . 'composer.phar dump-env test',
        ];

        foreach ($cwds as $cwd) {
            $process = Process::fromShellCommandline($cwd);
            $process->run();
            while ($process->isRunning()) {
                sleep(3);
            }
            if (!$process->isSuccessful()) {
                $error = new ProcessFailedException($process);
                //throwException($error);
                echo sprintf('%s : %s', $error->getCode(), $error->getMessage());
            }
        }
    }
}
