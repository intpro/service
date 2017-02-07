<?php

namespace Interpro\Service\Command\Artisan;

use Illuminate\Console\Command;
use Interpro\Service\Contracts\CleanMediator;
use Interpro\Service\Enum\Artefact;

class CleanDb extends Command
{
    private $mediator;

    /**
     * @var string
     */
    protected $signature = 'clean:db {--F|family=all}';

    /**
     * @var string
     */
    protected $description = 'Очистить таблицы пакетов от записей не соответствующих таксономии';

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
        $artefact = Artefact::DB_ROW;

        $family = $this->option('family');

        $this->info('Очистка базы для artefact = '.$artefact.', family = '.$family.' начата.');

        $report = $this->mediator->inspect($artefact, $family);

        if($report)
        {
            if ($this->confirm('Выполнить очистку базы от перечисленных записей? [y|N]'))
            {
                $this->mediator->clean($artefact, $family);
            }

            $this->info('Очистка базы для artefact = '.$artefact.', family = '.$family.' закончена.');
        }
        else
        {
            $this->info('Для artefact = '.$artefact.', family = '.$family.' некорректных записей не найдено.');
        }
    }

}
