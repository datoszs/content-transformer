<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Exceptions\ConfigurationException;
use DatosCZ\Transformer\Exceptions\UnexpectedTypeException;
use DatosCZ\Transformer\State\State;

class RegexExtract extends AGear
{
    const EXTRACTED = 1;
    const NOT_EXTRACTED = 2;

    /** @var string */
    private $regex;

    /** @var int */
    private $group;

    public function __construct($name, string $regex, int $group = 0)
    {
        parent::__construct($name);

        $this->regex = $regex;
        $this->group = $group;
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
        if ($matches && isset($matches[$this->group])) {
            $state->setContent(new StringContent($matches[$this->group]));
            $state->setState(self::EXTRACTED);
        } else {
            $state->setState(self::NOT_EXTRACTED);
        }

    }
}