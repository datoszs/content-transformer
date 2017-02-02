<?php
namespace DatosCZ\Transformer\Gears;


abstract class AGear implements IGear
{

    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}