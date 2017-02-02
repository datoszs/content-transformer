<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\State\State;

class ContentStater extends AGear
{
    const DONE = 1;

    /** @var string */
    private $message;

    /** @var bool */
    private $empty;

    public function __construct(string $name, string $message)
    {
        parent::__construct($name);

        $this->message = $message;
    }

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {
        $state->setContent(new StringContent($this->message));
        $state->setState(self::DONE);
    }
}