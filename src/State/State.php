<?php
namespace DatosCZ\Transformer\State;


use DatosCZ\Transformer\Content\IContent;
use DatosCZ\Transformer\Exceptions\NoSuchHistoryContentException;

class State
{
    /** @var IContent */
    private $content;

    /** @var IContent[] */
    private $contentHistory = [];

    /** @var int */
    private $state = 0;

    /** @var bool */
    private $finalized = false;

    /** @var string */
    private $errorState;

    public function __construct(IContent $content)
    {
        $this->contentHistory[] = $content;
        $this->content = $content;
    }

    public function getContent() : IContent
    {
        return $this->content;
    }

    public function setContent(IContent $content)
    {
        return $this->content = $content;
    }

    public function backupContent() : void
    {
        $this->contentHistory[] = clone $this->content;
    }

    public function restoreContent($backSteps = 1) : void
    {
        $index = count($this->contentHistory) - $backSteps;
        if (!isset($this->contentHistory[$index])) {
            throw new NoSuchHistoryContentException("No such history content [$index].");
        }
        $this->content = $this->contentHistory[$index];
        $this->contentHistory[] = clone $this->content;
    }

    public function getState() : int
    {
        return $this->state;
    }

    public function setState(int $state) : void
    {
        $this->state = $state;
    }

    public function isFinalized() : bool
    {
        return $this->finalized;
    }

    public function markFinalized() : void
    {
        $this->finalized = true;
    }

    public function setErrorState(string $errorState) : void
    {
        $this->errorState = $errorState;
    }

    public function getErrorState() : ?string
    {
        return $this->errorState;
    }
}