<?php
namespace Gears;


use DatosCZ\Transformer\Content\StringContent;
use DatosCZ\Transformer\Gears\Backup;
use DatosCZ\Transformer\Gears\Restore;
use DatosCZ\Transformer\State\State;


class RestoreTest extends \PHPUnit_Framework_TestCase
{

    public function testBasic()
    {
        $gear = new Backup('TEST');
        $this->assertEquals('TEST', $gear->getName());

        $instance = new StringContent('test content');
        $state = new State($instance);
        $this->assertTrue($gear->canProcess($state));
        $gear->process($state);
        $state->setContent(new StringContent('foo content'));

        $restoreGear = new Restore('TEST2');
        $this->assertEquals('TEST2', $restoreGear->getName());
        $this->assertTrue($restoreGear->canProcess($state));

        $restoreGear->process($state);

        $this->assertEquals($instance, $state->getContent());
    }
}
