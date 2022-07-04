<?php

declare(strict_types=1);

namespace Egal\Core\Console\Commands;

use Egal\Core\Exceptions\ReadingStudFileException;
use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Support\Str;

/**
 * Console command class designed to create console commands for generating files based on it.
 */
abstract class MakeCommand extends IlluminateCommand
{

    protected string $stubFileBaseName;

    /**
     * Contains the base name of the file - the result of the console command.
     */
    protected string $fileBaseName;

    protected string $fileContents;

    protected string $filePath;

    public function __construct()
    {
        parent::__construct();

        $stubFilesDir = realpath(__DIR__ . '/stubs');
        $this->fileContents = file_get_contents(realpath($stubFilesDir . '/' . $this->stubFileBaseName . '.stub'));

        if (!$this->fileContents) {
            throw new ReadingStudFileException();
        }
    }
 function writeFile(): void
    {
        if (file_exists($this->filePath) && !$this->confirm('File exists. Overwrite?')) {
            $this->warn('Canceled!');

            return;
        }

        if (!is_dir(dirname($this->filePath))) {
            mkdir(dirname($this->filePath));
        }

        file_put_contents($this->filePath, $this->fileContents);
        $file = Str::replaceFirst(base_path() . '/', '', $this->filePath);
        $this->line('<info>Result File:</info> ' . $file);
    }

    /**
     * Replaces a variable of the type {{ var }} with the value from the stub file.
     */
    public function setFileContents(string $variable, string $value): void
    {
        $this->fileContents = str_replace($variable, $value, $this->fileContents);
    }

}
