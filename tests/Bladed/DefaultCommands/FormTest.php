<?php
    namespace LaravelCommode\Bladed\DefaultCommands;

    class FormTest extends \PHPUnit_Framework_TestCase
    {
        protected function getAppMock()
        {
            return $this->getMock('Illuminate\Foundation\Application', ['make']);
        }

        protected function getFormMock()
        {
            return $this->getMock('Illuminate\Html\FormBuilder', [], [], '', 0);
        }

        protected function getInstance($app)
        {
            return new Form($app);
        }

        protected function getMetaMock($lang = 'en')
        {
            return $this->getMockForAbstractClass('LaravelCommode\Common\Meta\LocalizedMeta\MetaData', [$lang]);
        }

        public function testConstruct()
        {
            $app = $this->getAppMock();

            $app->expects($this->once())->method('make')->will($this->returnValue($form = $this->getFormMock()));

            $instance = $this->getInstance($app);

            $this->assertSame($form, $instance->getDelegate());
            $this->assertSame($app, $instance->getApplication());
        }

        public function testCreateElement()
        {
            $app = $this->getAppMock();

            $app->expects($this->once())->method('make')->will($this->returnValue($form = $this->getFormMock()));

            $instance = $this->getInstance($app);

            $reflectionClass = new \ReflectionClass($instance);
            $reflectionMethodCreateElement = $reflectionClass->getMethod('createElement');

            $reflectionMethodCreateElement->setAccessible(true);


            $this->assertSame("<div></div>", $reflectionMethodCreateElement->invokeArgs($instance, ['div'])->__toString());
            $this->assertSame("<input type=\"text\">", $reflectionMethodCreateElement->invokeArgs($instance, ['input'])->attr('type', 'text')->__toString());

            $this->assertSame($app, $instance->getApplication());
        }


        public function testMeta()
        {
            $app = $this->getAppMock();

            $app->expects($this->once())->method('make')->will($this->returnValue($form = $this->getFormMock()));

            $instance = $this->getInstance($app);

            $this->assertNull($instance->getMeta());
            $this->assertSame('<label>I\'m label</label>', $instance->meta('I\'m label')->__toString());

            $meta1 = $this->getMetaMock();
            $meta2 = $this->getMetaMock();

            $meta1->en_name = ($name1 = uniqid());
            $meta2->en_name = ($name2 = uniqid());

            $instance->setMeta($meta1);
            $instance->setMeta($meta2, 'span');

            $this->assertSame($meta2, $instance->getMeta());
            $this->assertSame("<span>{$name2}</span>", $instance->meta('name')->__toString());

            $instance->unsetMeta();

            $this->assertSame($meta1, $instance->getMeta());
            $this->assertSame("<label>{$name1}</label>", $instance->meta('name')->__toString());

            $instance->assignMeta($meta3);

            $this->assertSame($meta3, $meta1);

            $instance->unsetMeta();
            $this->assertNull($instance->getMeta());
        }


    }
