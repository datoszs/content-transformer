<?php
namespace Gears;


use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Gears\RegexMatch;
use DatosCZ\Transformer\State\State;


class RegexMatchTest extends \PHPUnit_Framework_TestCase
{

    public function testBasic()
    {
        $gear = new RegexMatch('TEST', '/e([^\s]*?)e/', 0);
        $this->assertEquals('TEST', $gear->getName());


        $content = new StringContent('some example string example exaaample abort abort end of sentence.');
        $state = new State($content);
        $this->assertTrue($gear->canProcess($state));
        $gear->process($state);
        $this->assertEquals('example', $state->getContent()->get());
    }
}
