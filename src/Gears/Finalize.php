<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\State\State;

class Finalize extends AGear
{
    const FINALIZED = 1;

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {
        $state->markFinalized();
        $state->setState(self::FINALIZED);
    }
}