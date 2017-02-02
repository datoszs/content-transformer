<?php
namespace DatosCZ\Transformer\Utils;


use DatosCZ\Transformer\Exceptions\DuplicateGearException;
use DatosCZ\Transformer\Exceptions\DuplicateTransitionException;
use DatosCZ\Transformer\Exceptions\NoStartGearException;
use DatosCZ\Transformer\Exceptions\NoSuchGearException;
use DatosCZ\Transformer\Gears\AGear;
use DatosCZ\Transformer\Gears\IGear;
use DatosCZ\Transformer\State\State;
use Exception;

class GearBox
{
    /** @var array */
    private $states = [];
    /** @var array string:IGear */
    private $gears = [];

    /** @var AGear */
    private $start;

    /** @var bool */
    private $debugMode = false;

    public function setStart($name) : void
    {
        if (isset($this->gears[$name])) {
            $this->start = $this->gears[$name];
        } else {
            throw new NoSuchGearException("The gear with name [" . $name . "] doesn't exist in this gear box.");
        }
    }

    public function addGear(AGear $gear) : void
    {
        if (isset($this->gears[$gear->getName()])) {
            throw new DuplicateGearException("The gear with name [" . $gear->getName() . "] already exists in this gear box.");
        }
        $this->gears[$gear->getName()] = $gear;
    }

    public function getGear(string $name) : IGear
    {
        if (isset($this->gears[$name])) {
            return $this->gears[$name];
        }
        throw new NoSuchGearException("The gear with name [" . $name . "] doesn't exist in this gear box.");
    }

    public function existGear(string $name) : bool
    {
        return isset($this->gears[$name]);
    }

    public function addTransition(string $from, int $state, string $to) : void
    {
        if (!$this->existGear($from) || !$this->existGear($to)) {
            throw new NoSuchGearException("The gear with name [${from}] or [${to}] doesn't exist in this tagger.");
        }
        if (isset($this->states[$from]) && isset($this->states[$from][$state])) {
            throw new DuplicateTransitionException("The transition from [${from}] under state [${state}] is already defined.");
        }
        if (!isset($this->states[$from])) {
            $this->states[$from] = [];
        }
        $this->states[$from][$state] = $to;
    }

    public function setDebugMode(bool $state) : void
    {
        $this->debugMode = $state;
    }

    public function isDebugMode() : bool
    {
        return $this->debugMode;
    }

    public function process(State $state) : void
    {
        if (!$this->start) {
            throw new NoStartGearException('This gear box has no configured start gear.');
        }
        /** @var AGear $gear */
        $gear = $this->start;
        while ($gear != null) {
            // Check whether the current gear can process
            try {
                $gear->canProcess($state);
            } catch (Exception $ex) {
                printf("[%s] <CANNOT PROCESS: %s", $gear->getName(), $ex->getMessage() . ">\n");
                return;
            }
            // Process
            try {
                $gear->process($state);
            } catch (Exception $ex) {
                printf("[%s] <PROCESSING FAILURE: %s", $gear->getName(), $ex->getMessage() . ">\n");
                return;
            }
            if ($this->debugMode) {
                print("\n");
                printf("IN: [%s] <STATE: %s>", $gear->getName(), $state->getState());
            }
            // Move to the next gear according to state diagram (or finalized state)
            if ($state->isFinalized()) {
                $gear = null;
            } else if (isset($this->states[$gear->getName()]) && isset($this->states[$gear->getName()][$state->getState()])) {
                $gear = $this->getGear($this->states[$gear->getName()][$state->getState()]);
            } else {
                $gear = null;
                if ($this->debugMode) {
                    print_r($this->states);
                }
            }
            if ($this->debugMode) {
                if ($gear) {
                    print "TRANSITION TO: ". $gear->getName();
                } else {
                    print "TRANSITION TO: <NOWHERE>\n";
                }
            }
        }
    }
}