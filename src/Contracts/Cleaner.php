<?php

namespace Interpro\Service\Contracts;

use Interpro\Core\Contracts\Mediatable;

interface Cleaner extends Mediatable
{
    /**
     * @return bool
     */
    public function inspect();

    /**
     * @return void
     */
    public function clean();

    /**
     * @return string
     */
    public function getArtefact();
}
