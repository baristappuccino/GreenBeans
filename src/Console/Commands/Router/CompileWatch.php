<?php

namespace GreenBeans\Console\Commands\Router;

use GreenBeans\Console\Command;
use GreenBeans\Util\Base;

class CompileWatch extends Command
{

    private string $checksum = "";
    private string $baseDir;

    /**
     * @inheritDoc
     */
    public function run(array $args): void
    {
        $this->baseDir = Base::get() . '/app/Controllers';
        $this->warn("Do not run this command in production, as it periodically locks the routes_c.json file.");

        do {
            $checksum = $this->getDirectoryChecksum($this->baseDir);
            if ($checksum !== $this->checksum) {
                $this->checksum = $checksum;
                $this->compile();
            }
        } while (sleep(1) === 0);
    }

    /**
     * Re-compile routes
     */
    private function compile(): void
    {
        Compile::invoke([]);
    }

    /**
     * Get md5 checksum from a directory
     * @param string $dir
     * @return string
     */
    public function getDirectoryChecksum(string $dir): string
    {
        $fileChecksums = array();
        $d = dir($dir);

        while (false !== ($entry = $d->read())) {
            if ($entry !== '.' && $entry !== '..') {
                if (is_dir($dir . '/' . $entry)) {
                    $fileChecksums[] = $this->getDirectoryChecksum($dir . '/' . $entry);
                } else {
                    $fileChecksums[] = md5_file($dir . '/' . $entry);
                }
            }
        }
        $d->close();
        return md5(implode('', $fileChecksums));
    }

    /**
     * @inheritDoc
     */
    public static function invoke(array $args): void
    {
        (new self())->run($args);
    }
}
