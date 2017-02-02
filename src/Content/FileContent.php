<?php
namespace DatosCZ\Transformer\Content;


class FileContent implements IContent
{
    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /** @inheritdoc */
    public function get() : string
    {
        return $this->filePath;
    }

    /** @inheritdoc */
    public function set(string $content) : void
    {
        $this->filePath = $content;
    }
}