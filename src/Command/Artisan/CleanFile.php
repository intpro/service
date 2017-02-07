<?php

namespace Interpro\Service\Command\Artisan;

use Illuminate\Console\Command;
use Interpro\Service\Contracts\CleanMediator;
use Interpro\Service\Enum\Artefact;

class CleanFile extends Command
{
    private $mediator;

    /**
     * @var string
     */
    protected $signature = 'clean:file {--F|family=all}';

    /**
     * @var string
     */
    protected $description = 'Удалить сгенерированные пакетами файлы на которые нет ссылок в базе.';

    /**
     * @return void
     */
    public function __construct(CleanMediator $mediator)
    {
        parent::__construct();

        $this->mediator = $mediator;
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $artefact = Artefact::FILE;

        $family = $this->option('family');

        $this->info('Очистка базы для artefact = '.$artefact.', family = '.$family.' начата.');

        $report = $this->mediator->inspect($artefact, $family);

        if($report)
        {
            if ($this->confirm('Удалить перечисленные файлы? [y|N]'))
            {
                $this->mediator->clean($artefact, $family);
            }

            $this->info('Удаление файлов для artefact = '.$artefact.', family = '.$family.' закончена.');
        }
        else
        {
            $this->info('Для artefact = '.$artefact.', family = '.$family.' некорректных лишних файлов не найдено.');
        }
    }

}
