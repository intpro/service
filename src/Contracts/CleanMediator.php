<?php

namespace Interpro\Service\Contracts;

interface CleanMediator
{
    /**
     * @param string $artefact
     * @param string $family
     * @param bool $console
     *
     * @return \Interpro\Service\Contracts\Cleaner
     */
    public function getCleaner($artefact, $family, $console = true);

    /**
     * @param string $artefact
     * @param string $family
     *
     * @return void
     */
    public function clean($artefact = 'all', $family = 'all');

    /**
     * @param string $artefact
     * @param string $family
     *
     * @return bool
     */
    public function inspect($artefact = 'all', $family = 'all');

    /**
     * @param \Interpro\Service\Contracts\DbCleaner
     *
     * @return void
     */
    public function registerCleaner(Cleaner $cleaner);
}
