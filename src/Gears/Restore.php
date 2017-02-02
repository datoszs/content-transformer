<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\State\State;

class Restore extends AGear
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
        $state->restoreContent();
        $state->setState(self::DONE);
    }
}