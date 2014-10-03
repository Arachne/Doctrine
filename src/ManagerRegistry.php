<?php

namespace Arachne\Doctrine;

use Kdyby\Doctrine\DI\OrmExtension;
use Doctrine\ORM\ORMException;
use Nette\DI\Container;

/**
 * Implementation of the ManagerRegistry contract using Nette DIC.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Jáchym Toušek
 */
class ManagerRegistry implements \Doctrine\Common\Persistence\ManagerRegistry
{

	const PROXY_INTERFACE_NAME = 'Doctrine\ORM\Proxy\Proxy';

	/** @var array */
	private $connections;

	/** @var string */
	private $defaultConnection;

	/** @var array */
	private $managers;

	/** @var string */
	private $defaultManager;

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(Container $container)
	{
		$this->connections = array_keys($container->findByTag(OrmExtension::TAG_CONNECTION));
		$this->defaultConnection = $container->findByType('Doctrine\DBAL\Connection');
		$this->managers = array_keys($container->findByTag(OrmExtension::TAG_ENTITY_MANAGER));
		$this->defaultManager = $container->findByType('Doctrine\ORM\EntityManager');
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConnection($name = null)
	{
		if (null === $name) {
			$name = $this->defaultConnection;
		}

		if (!isset($this->connections[$name])) {
			throw new \InvalidArgumentException(sprintf('Doctrine Connection named "%s" does not exist.', $name));
		}

		return $this->container->getService($this->connections[$name]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConnectionNames()
	{
		return $this->connections;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConnections()
	{
		$connections = array();
		foreach ($this->connections as $name => $id) {
			$connections[$name] = $this->container->getService($id);
		}

		return $connections;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultConnectionName()
	{
		return $this->defaultConnection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultManagerName()
	{
		return $this->defaultManager;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getManager($name = null)
	{
		if (null === $name) {
			$name = $this->defaultManager;
		}

		if (!isset($this->managers[$name])) {
			throw new \InvalidArgumentException(sprintf('Doctrine Manager named "%s" does not exist.', $name));
		}

		return $this->container->getService($this->managers[$name]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getManagerForClass($class)
	{
		// Check for namespace alias
		if (strpos($class, ':') !== false) {
			list($namespaceAlias, $simpleClassName) = explode(':', $class);
			$class = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
		}

		$proxyClass = new \ReflectionClass($class);
		if ($proxyClass->implementsInterface(self::PROXY_INTERFACE_NAME)) {
			$class = $proxyClass->getParentClass()->getName();
		}

		foreach ($this->managers as $id) {
			$manager = $this->container->getService($id);

			if (!$manager->getMetadataFactory()->isTransient($class)) {
				return $manager;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getManagerNames()
	{
		return $this->managers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getManagers()
	{
		$dms = array();
		foreach ($this->managers as $name => $id) {
			$dms[$name] = $this->container->getService($id);
		}

		return $dms;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRepository($persistentObjectName, $persistentManagerName = null)
	{
		return $this->getManager($persistentManagerName)->getRepository($persistentObjectName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function resetManager($name = null)
	{
		if (null === $name) {
			$name = $this->defaultManager;
		}

		if (!isset($this->managers[$name])) {
			throw new \InvalidArgumentException(sprintf('Doctrine Manager named "%s" does not exist.', $name));
		}

		// force the creation of a new document manager
		// if the current one is closed
		$this->container->removeService($this->managers[$name]);
	}

	/**
	 * Resolves a registered namespace alias to the full namespace.
	 * This method looks for the alias in all registered entity managers.
	 *
	 * @param string $alias The alias
	 * @return string The full namespace
	 */
	public function getAliasNamespace($alias)
	{
		foreach (array_keys($this->getManagers()) as $name) {
			try {
				return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
			} catch (ORMException $e) {
			}
		}

		throw ORMException::unknownEntityNamespace($alias);
	}

}
