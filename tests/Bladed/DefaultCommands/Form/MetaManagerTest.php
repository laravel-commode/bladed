<?php
    namespace LaravelCommode\Bladed\DefaultCommands\Form\MetaManager;

    use LaravelCommode\Bladed\DefaultCommands\Form\MetaManager;
    use LaravelCommode\Common\Meta\LocalizedMeta\MetaData;

    class MetaManagerTest extends \PHPUnit_Framework_TestCase
    {
        protected function getInstance()
        {
            return new MetaManager();
        }

        /**
         * @param string $lang
         * @return \PHPUnit_Framework_MockObject_MockObject|MetaData
         */
        protected function getMetaMock($lang = 'en')
        {
            return $this->getMockForAbstractClass(
                'LaravelCommode\Common\Meta\LocalizedMeta\MetaData', [$lang]
            );
        }

        public function testEmptyMeta()
        {
            $instance = $this->getInstance();

            $this->assertFalse($instance->currentMetaExists());

            $key = uniqid();
            $this->assertSame("<!-- {$key} -->",$instance->getCurrentMetaValue($key));
        }

        public function testAddUnsetMeta()
        {
            $instance = $this->getInstance();
            $meta = $this->getMetaMock();
            $meta2 = $this->getMetaMock();

            $type = 'label';

            $instance->addMeta($meta, $type);
            $instance->addMeta($meta2, $type);

            $this->assertSame($type, $instance->getCurrentElement());
            $this->assertSame($meta2, $instance->getCurrentMeta());

            $instance->unsetCurrentMeta();

            $this->assertTrue($instance->currentMetaExists());

            $meta->en_hello = ($value = uniqid());

            $this->assertSame($value, $instance->getCurrentMetaValue('hello'));;

            $instance->unsetCurrentMeta();
            $this->assertFalse($instance->currentMetaExists());
        }
    }
