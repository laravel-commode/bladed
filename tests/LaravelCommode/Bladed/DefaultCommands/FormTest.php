<?php

namespace LaravelCommode\Bladed\DefaultCommands;

use Exception;
use Illuminate\Html\FormBuilder;

use InvalidArgumentException;
use LaravelCommode\Bladed\DefaultCommands\Form\MetaQueManager;
use LaravelCommode\Utils\Meta\Localization\MetaAttributes;
use LaravelCommode\Utils\Tests\PHPUnitContainer;

use LaravelCommode\Utils\UtilsServiceProvider;
use LogicException;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class FormTest extends PHPUnitContainer
{
    /**
     * @var Form
     */
    private $testInstance;

    /**
     * @var FormBuilder|Mock
     */
    private $formBuilderMock;

    /**
     * @var MetaQueManager
     */
    private $metaQueManager;

    /**
     * @var MetaAttributes|Mock
     */
    private $metaAttributesMock;

    /**
     * @var MetaAttributes|Mock
     */
    private $metaAttributesMock1;

    protected function applicationMocksMethods()
    {
        return ['getLocale'];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->formBuilderMock = $this->getMock(FormBuilder::class, [], [], '', false);
        $this->metaQueManager = new MetaQueManager();

        $this->metaAttributesMock = $this->getMockForAbstractClass(MetaAttributes::class);
        $this->metaAttributesMock1 = $this->getMockForAbstractClass(MetaAttributes::class);

        $this->testInstance = new Form($this->getApplicationMock());
    }

    public function testGetDelegate()
    {
        $this->getApplicationMock()->expects($this->exactly(1))->method('make')
            ->with('form')
            ->will($this->returnValue($this->formBuilderMock));

        $this->assertSame($this->formBuilderMock, $this->testInstance->getDelegate());
        $this->assertSame($this->formBuilderMock, $this->testInstance->getDelegate());
    }

    public function testMetas()
    {
        $this->assertNull($this->testInstance->getMeta());
        $this->testInstance->assignMeta($nullMeta);
        $this->assertNull($nullMeta);

        $this->testInstance->setMeta($this->metaAttributesMock);
        $this->assertSame($this->metaAttributesMock, $this->testInstance->getMeta());
        $this->testInstance->assignMeta($notNullMeta);
        $this->assertSame($notNullMeta, $this->testInstance->getMeta());

        $this->testInstance->setMeta($this->metaAttributesMock1);
        $this->assertSame($this->metaAttributesMock1, $this->testInstance->getMeta());

        $this->testInstance->unsetMeta();

        $this->assertSame($this->metaAttributesMock, $this->testInstance->getMeta());

        $this->metaAttributesMock->value = uniqid();

        $this->assertSame(
            "<label>{$this->metaAttributesMock->value}</label>",
            (string) $this->testInstance->meta('value', '', '')
        );

        $this->testInstance->unsetMeta();

        try {
            $this->testInstance->meta('value', '', '');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof LogicException);
        }
    }

    public function testModels()
    {
        $this->getApplicationMock()->expects($this->exactly(1))->method('make')
            ->with('form')
            ->will($this->returnValue($this->formBuilderMock));

        $this->assertNull($this->testInstance->getModel());

        $this->testInstance->setModel((object)['a' => 1]);

        $this->testInstance->model((object)['a' => 2]);

        $this->testInstance->unsetModel();
    }

    public function testElements()
    {
        $this->getApplicationMock()->expects($this->exactly(1))->method('make')
            ->with('form')
            ->will($this->returnValue($this->formBuilderMock));

        $this->testInstance->setMeta($this->metaAttributesMock);

        $this->metaAttributesMock->__value = uniqid();
        $this->metaAttributesMock->setLocale('_');

        $testMethods = [
            'open', 'select', 'close', 'submit', 'hidden', 'checkbox', 'radio',
            'text', 'password', 'textarea'
        ];

        foreach ($testMethods as $testMethod) {
            $this->formBuilderMock->expects($this->exactly(1))->method($testMethod);
        }

        $this->assertSame("<label>label</label>", (string)$this->testInstance->label('label'));

        $this->testInstance->open();
        $this->testInstance->close();
        $this->testInstance->select('value');
        $this->testInstance->submit('value');
        $this->testInstance->hidden('value');
        $this->testInstance->checkbox('value');
        $this->testInstance->radio('value');
        $this->testInstance->text('value');
        $this->testInstance->textarea('value');
        $this->testInstance->password('value');

    }

    protected function tearDown()
    {
        unset($this->testInstance);
        parent::tearDown();
    }
}
