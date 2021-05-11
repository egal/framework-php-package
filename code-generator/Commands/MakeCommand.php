<?php

namespace Egal\CodeGenerator\Commands;

use Egal\CodeGenerator\Exceptions\ReadingStudFileException;
use Exception;
use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Support\Str;

/**
 * Класс консольной команды предназначенный для создания на его основе консольных команд генерации файлов
 *
 * @package Egal\Model
 */
abstract class MakeCommand extends IlluminateCommand
{

    /**
     * Содержит базовое название файла-заглушки
     *
     * @var string
     */
    protected string $stubFileBaseName;

    /**
     * Содержит базовое название файла - результата работы консольной команды
     *
     * @var string
     */
    protected string $fileBaseName;

    /**
     * @var string
     */
    protected string $fileContents;

    protected string $filePath;

    /**
     * Инициализатор экземпляра класса
     *
     * Присваивает {@see MakeCommand::$fileContents} содержание файла-заглушки,
     * указанного в {@see MakeCommand::$stubFileBaseName}.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $stubFilesDir = realpath(__DIR__ . '/../../stubs');
        $this->fileContents = file_get_contents(realpath($stubFilesDir . '/' . $this->stubFileBaseName . '.stub'));
        if (!$this->fileContents) {
            throw new ReadingStudFileException();
        }
    }

    /**
     * Записывает файл
     *
     * Записывает содержание {@see MakeCommand::$fileContents} в файл,
     * путь которого указан в {@see MakeCommand::$filePath}.
     *
     * Если такого файла нет - создает его.
     *
     * @throws Exception
     */
    public function writeFile(): void
    {
        if (file_exists($this->filePath) && !$this->confirm('Файл существует. Перезаписать?')) {
            $this->warn('Отмена!');
            return;
        }
        if (!is_dir(dirname($this->filePath))) mkdir(dirname($this->filePath));
        file_put_contents($this->filePath, $this->fileContents);
        $file = Str::replaceFirst(base_path() . '/', '', $this->filePath);
        $this->line('<info>Result File:</info> ' . $file);
    }

    /**
     * Заменяет переменную вида {{ var }} значением в файле заглушке
     *
     * @param string $variable
     * @param string $value
     */
    public function setFileContents(string $variable, string $value)
    {
        $this->fileContents = str_replace($variable, $value, $this->fileContents);
    }

}
