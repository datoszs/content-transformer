<?php
namespace DatosCZ\Transformer\Content;


interface IContent
{

    /**
     * Return content
     *
     * @return mixed
     */
    public function get();

    /**
     * Replaces content with given one
     *
     * @param mixed $content
     * @return mixed
     */
    public function set(string $content) : void;
}