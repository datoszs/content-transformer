<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Exceptions\ConfigurationException;
use DatosCZ\Transformer\Exceptions\UnexpectedTypeException;
use DatosCZ\Transformer\State\State;

class RegexSplit extends AGear
{
    const FOUND = 1;
    const NOT_FOUND = 2;

    const LEFT = 1;
    const RIGHT = 2;

    /** @var String */
    private $regex;
    /** @var int */
    private $keep;

    public function __construct($name, string $regex, $keep = self::LEFT)
    {
        parent::__construct($name);

        $this->regex = $regex;
        if (!in_array($keep, [self::LEFT, self::RIGHT])) {
            throw new ConfigurationException('Unknown setting for keep flag.');
        }
        $this->keep = $keep;
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
        preg_match($this->regex, $state->getContent()->get(), $matches, PREG_OFFSET_CAPTURE);
        if ($matches && isset($matches[0])) {
            $state->setState(self::FOUND);
            if ($this->keep === self::LEFT) {
                $state->setContent(new StringContent(substr($state->getContent()->get(), 0, $matches[0][1])));
            } else {
                $state->setContent(new StringContent(substr($state->getContent()->get(), $matches[0][1] + strlen($matches[0][0]), strlen($state->getContent()->get()))));
            }
        } else {
            $state->setState(self::NOT_FOUND);
        }

    }
}