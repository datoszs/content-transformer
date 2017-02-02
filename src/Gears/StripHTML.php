<?php

namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\Content\HTMLContent;
use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Exceptions\UnexpectedTypeException;
use DatosCZ\Transformer\State\State;
use HTMLPurifier;
use HTMLPurifier_Config;

class StripHTML extends AGear
{
    const STRIPPED = 1;

    /** @var bool */
    private $normalizeWhitespace;

    /** @var HTMLPurifier */
    private $purifier;

    public function __construct($name, $normalizeWhitespace = false)
    {
        parent::__construct($name);
        $this->normalizeWhitespace = (bool) $normalizeWhitespace;
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', '');
        $this->purifier = new HTMLPurifier($config);
    }

    /** @inheritdoc */
    public function canProcess(State $state) : bool
    {
        if (!$state->getContent() instanceof HTMLContent) {
            throw new UnexpectedTypeException("Unexpected type, HTML content expected.");
        }
        return true;
    }

    /** @inheritdoc */
    public function process(State $state) : void
    {

        $text = $this->purifier->purify($state->getContent()->get());
        if ($this->normalizeWhitespace) {
            $text = preg_replace('/[\s]{1,}/', ' ', trim($text));
        }
        $state->setContent(new StringContent($text));
        $state->setState(self::STRIPPED);
    }
}