<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\State\State;

class ErrorStater extends AGear
{
    const DONE = 1;

    /** @var string */
    private $message;

    /** @var bool */
    private $empty;

    public function __construct(string $name, string $message, bool $empty = false)
    {
        parent::__construct($name);

        $this->message = $message;
        $this->empty = $empty;
    }

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {
        if ($this->empty) {
            $className = get_class($state->getContent());
            $state->setContent(new $className());
        }
        $state->setErrorState($this->message);
        $state->setState(self::DONE);
    }
}