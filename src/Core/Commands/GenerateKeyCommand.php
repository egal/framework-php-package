<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Egal\Core\Commands;

use Illuminate\Console\Command;

class GenerateKeyCommand extends Command
{

    protected $signature = 'egal:key:generate
                                {--s|show : Показать ключ}
                           ';

    protected $description = '';

    public function handle(): void
    {
        $seed = str_split(
            'abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789'
            . '@#%^*'
        );
        shuffle($seed);
        $key = '';
        foreach (array_rand($seed, 32) as $k) {
            $key .= $seed[$k];
        }

        if ($this->option('show')) {
            $this->line("Секретный ключ: <info>$key</info>");
        }
    }

}
