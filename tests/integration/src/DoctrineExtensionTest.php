<?php

namespace Tests\Integration;

use Arachne\Bootstrap\Configurator;
use Codeception\TestCase\Test;
use Doctrine\Common\EventManager;
use Symfony\Bridge\Doctrine\ContainerAwareEventManager;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DoctrineExtensionTest extends Test
{
    /**
     * @expectedException Nette\Utils\AssertionException
     * @expectedExceptionMessage Subscribers support requires 'Arachne\ContainerAdapter\DI\ContainerAdapterExtension' to be installed and 'Kdyby\Events\DI\EventsExtension' to NOT be installed.
     */
    public function testSubscriberException()
    {
        $this->createContainer('subscriber-exception.neon');
    }

    public function testSubscriber()
    {
        $container = $this->createContainer('subscriber.neon');

        /* @var $evm EventManager */
        $evm = $container->getByType(EventManager::class);
        $this->assertInstanceOf(ContainerAwareEventManager::class, $evm);

        // Ensure laziness.
        $this->assertFalse($container->isCreated('subscriber'));
        $evm->dispatchEvent('fooEvent');
        $this->assertFalse($container->isCreated('subscriber'));
        $evm->dispatchEvent('barEvent');
        $this->assertTrue($container->isCreated('subscriber'));
    }

    private function createContainer($file)
    {
        $config = new Configurator();
        $config->setTempDirectory(TEMP_DIR);
        $config->addConfig(__DIR__ . '/../config/' . $file, false);
        return $config->createContainer();
    }
}
