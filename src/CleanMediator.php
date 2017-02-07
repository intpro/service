<?php

namespace Interpro\Service;

use Interpro\Service\Contracts\Cleaner;
use Interpro\Service\Contracts\CleanMediator as CleanMediatorInterface;
use Interpro\Service\Exception\ServiceException;

class CleanMediator implements CleanMediatorInterface
{
    private $cleaners = [];
    private $cleaner_by_family = [];
    private $cleaner_by_art = [];
    private $consoleOutput;

    public function __construct()
    {
        $this->consoleOutput = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    /**
     * @param string $artefact
     * @param string $family
     * @param bool $console
     *
     * @return \Interpro\Service\Contracts\Cleaner
     */
    public function getCleaner($artefact, $family, $console = true)
    {
        if(!array_key_exists($family.'_'.$artefact, $this->cleaners))
        {
            if($console)
            {
                $this->consoleOutput->writeln('Чистильщик пакета '.$family.'('.$artefact.') не найден в медиаторе!');

                return null;
            }
            else
            {
                throw new ServiceException('Чистильщик пакета '.$family.'('.$artefact.') не найден в медиаторе!');
            }
        }

        return $this->cleaners[$family.'_'.$artefact];
    }

    /**
     * @param string $artefact
     * @param string $family
     *
     * @return void
     */
    public function clean($artefact = 'all', $family = 'all')
    {
        if($artefact === 'all' and $family === 'all')
        {
            foreach($this->cleaners as $cleaner)
            {
                $cleaner->clean();
            }
        }
        elseif($artefact === 'all' and $family !== 'all')
        {
            if(array_key_exists($family, $this->cleaner_by_family))
            {
                foreach($this->cleaner_by_family[$family] as $cleaner)
                {
                    $cleaner->clean();
                }
            }
            else
            {
                $this->consoleOutput->writeln('Чистильщиков для пакета '.$family.' не найдено в медиаторе!');
            }
        }
        elseif($artefact !== 'all' and $family === 'all')
        {
            if(array_key_exists($artefact, $this->cleaner_by_art))
            {
                foreach($this->cleaner_by_art[$artefact] as $cleaner)
                {
                    $cleaner->clean();
                }
            }
            else
            {
                $this->consoleOutput->writeln('Чистильщиков для артефактов '.$artefact.' не найдено в медиаторе!');
            }
        }
        elseif($artefact !== 'all' and $family !== 'all')
        {
            $cleaner = $this->getCleaner($artefact, $family);

            if($cleaner)
            {
                $cleaner->clean();
            }
        }
    }

    /**
     * @param string $artefact
     * @param string $family
     *
     * @return bool
     */
    public function inspect($artefact = 'all', $family = 'all')
    {
        $report = false;

        if($artefact === 'all' and $family === 'all')
        {
            foreach($this->cleaners as $cleaner)
            {
                $report = ($report or $cleaner->inspect());
            }
        }
        elseif($artefact === 'all' and $family !== 'all')
        {
            if(array_key_exists($family, $this->cleaner_by_family))
            {
                foreach($this->cleaner_by_family[$family] as $cleaner)
                {
                    $report = ($report or $cleaner->inspect());
                }
            }
            else
            {
                $this->consoleOutput->writeln('Чистильщиков для пакета '.$family.' не найдено в медиаторе!');
            }
        }
        elseif($artefact !== 'all' and $family === 'all')
        {
            if(array_key_exists($artefact, $this->cleaner_by_art))
            {
                foreach($this->cleaner_by_art[$artefact] as $cleaner)
                {
                    $report = ($report or $cleaner->inspect());
                }
            }
            else
            {
                $this->consoleOutput->writeln('Чистильщиков для артефактов '.$artefact.' не найдено в медиаторе!');
            }
        }
        elseif($artefact !== 'all' and $family !== 'all')
        {
            $cleaner = $this->getCleaner($artefact, $family);

            if($cleaner)
            {
                $report = ($report or $cleaner->inspect());
            }
        }

        return $report;
    }

    /**
     * @param \Interpro\Service\Contracts\DbCleaner
     *
     * @return void
     */
    public function registerCleaner(Cleaner $cleaner)
    {
        $family = $cleaner->getFamily();
        $artefact = $cleaner->getArtefact();

        if(array_key_exists($family.'_'.$artefact, $this->cleaners))
        {
            throw new ServiceException('Чистильщик пакета '.$family.'('.$artefact.') уже зарегестрирована в медиаторе!');
        }

        $this->cleaners[$family.'_'.$artefact] = $cleaner;
        $this->cleaner_by_family[$family][$artefact] = $cleaner;
        $this->cleaner_by_art[$artefact][$family] = $cleaner;
    }
}
