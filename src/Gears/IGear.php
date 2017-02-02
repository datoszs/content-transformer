<?php
namespace DatosCZ\Transformer\Gears;


use DatosCZ\Transformer\State\State;

interface IGear
{

    /**
     * Returns whether the gear is ready (e.g. configured properly, and the input content is of proper type).
     *
     * @param $state State to check against
     * @return bool
     */
    public function canProcess(State $state) : bool;

    /**
     * Perform gear operation on given state.
     *
     * @param $state State to transform
     */
    public function process(State $state) : void;
}