<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\Content\HTMLContent;
use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Exceptions\UnexpectedTypeException;
use DatosCZ\Transformer\State\State;

class CastToHTML extends AGear
{
    const CASTED = 1;

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        if (!$state->getContent() instanceof StringContent) {
            throw new UnexpectedTypeException("Unexpected type, string content expected.");
        }
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {
        $state->setContent(new HTMLContent($state->getContent()->get()));
        $state->setState(self::CASTED);
    }
}