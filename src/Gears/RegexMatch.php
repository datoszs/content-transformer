<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Exceptions\ConfigurationException;
use DatosCZ\Transformer\Exceptions\UnexpectedTypeException;
use DatosCZ\Transformer\State\State;

class RegexMatch extends AGear
{
    const MATCHED = 1;
    const NOT_MATCHED = 2;

    /** @var String */
    private $regex;

    public function __construct($name, string $regex)
    {
        parent::__construct($name);

        $this->regex = $regex;
    }

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        if (!$this->regex) {
            throw new ConfigurationException('Regex was not provided.');
        }
        if (!$state->getContent() instanceof StringContent) {
            throw new UnexpectedTypeException('Unexpected content type, file content expected.');
        }
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {
        preg_match($this->regex, $state->getContent()->get(), $matches);
        if ($matches && isset($matches[0])) {
            $state->setState(self::MATCHED);
        } else {
            $state->setState(self::NOT_MATCHED);
        }

    }
}