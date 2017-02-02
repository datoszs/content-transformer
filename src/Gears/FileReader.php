<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\Content\FileContent;
use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Exceptions\RuntimeException;
use DatosCZ\Transformer\Exceptions\UnexpectedTypeException;
use DatosCZ\Transformer\State\State;

class FileReader extends AGear
{
    const LOADED = 1;

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        if (!$state->getContent() instanceof FileContent) {
            throw new UnexpectedTypeException('Unexpected content type, file content expected.');
        }
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {
        $content = @file_get_contents($state->getContent()->get());
        if ($content === false) {
            throw new RuntimeException(sprintf("File [%s] could not be read.", $state->getContent()->get()));
        }
        $state->setContent(new StringContent($content));
        $state->setState(self::LOADED);
    }
}