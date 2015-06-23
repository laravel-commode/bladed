<?php

namespace LaravelCommode\Bladed\DefaultCommands\Form;

use LaravelCommode\Utils\Meta\Localization\MetaAttributes;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class MetaManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MetaQueManager
     */
    private $testInstance;

    /**
     * @var MetaAttributes|Mock
     */
    private $metaMock1;

    /**
     * @var MetaAttributes|Mock
     */
    private $metaMock2;

    private $mockValues = [];

    protected function setUp()
    {
        $this->testInstance = new MetaQueManager();

        $this->metaMock1 = $this->getMockForAbstractClass(MetaAttributes::class, ['_']);
        $this->metaMock2 = $this->getMockForAbstractClass(MetaAttributes::class, ['_']);

        $this->mockValues = [uniqid(), uniqid()];

        $this->metaMock1->__login = $this->mockValues[0];
        $this->metaMock2->__login = $this->mockValues[1];

        parent::setUp();
    }

    public function testMetaQue()
    {
        $this->assertSame('<!-- _l_ogin -->', $this->testInstance->getCurrentMetaValue('_l_ogin'));

        $this->testInstance->addMeta($this->metaMock1);

        $this->assertSame('label', $this->testInstance->getCurrentElement());
        $this->assertSame($this->mockValues[0], $this->testInstance->getCurrentMetaValue('login'));

        $this->testInstance->addMeta($this->metaMock2, 'span');

        $this->assertSame('span', $this->testInstance->getCurrentElement());
        $this->assertSame($this->mockValues[1], $this->testInstance->getCurrentMetaValue('login'));

        $this->testInstance->unsetCurrentMeta();

        $this->assertSame($this->mockValues[0], $this->testInstance->getCurrentMetaValue('login'));
    }

    protected function tearDown()
    {
        unset($this->testInstance);
        parent::tearDown();
    }
}
