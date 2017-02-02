<?php
namespace DatosCZ\Transformer\Content;


class HTMLContent implements IContent
{
    /** @var string */
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /** @inheritdoc */
    public function get() : string
    {
        return $this->content;
    }

    /** @inheritdoc */
    public function set(string $content) : void
    {
        $this->content = $content;
    }
}