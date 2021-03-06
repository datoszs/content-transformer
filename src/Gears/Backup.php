<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\State\State;

class Backup extends AGear
{
    const DONE = 1;

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {
        $state->backupContent();
        $state->setState(self::DONE);
    }
}