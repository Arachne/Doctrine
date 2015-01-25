<?php

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Arachne\Doctrine\Exception\InvalidArgumentException;
use Arachne\EntityLoader\ConverterInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Nette\Application\BadRequestException;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DoctrineConverter extends Object implements ConverterInterface
{

	/** @var ManagerRegistry */
	protected $managerRegistry;

	/** @var EntityRepository[] */
	private $repositories;

	/** @var EntityManager[] */
	private $managers;

	/**
	 * @param ManagerRegistry
	 */
	public function __construct(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->repositories = array();
		$this->managers = array();
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function entityExists($type)
	{
		return (bool) $this->getRepository($type);
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return object
	 * @throws BadRequestException
	 */
	public function filterIn($type, $value)
	{
		$repository = $this->getRepository($type);
		if ($value instanceof QueryInterface) {
			$entity = $value->getEntity($repository);
		} elseif (is_array($value)) {
			$entity = $repository->findOneBy($value);
		} else {
			$entity = $repository->find($value);
		}
		if (!$entity instanceof $type) {
			throw new BadRequestException("Desired entity of type '$type' could not be found.");
		}
		return $entity;
	}

	/**
	 * @param string $type
	 * @param object $entity
	 * @return string
	 */
	public function filterOut($type, $entity)
	{
		if (!$entity instanceof $type) {
			throw new InvalidArgumentException("Given entity is not instance of '$type'.");
		}
		$this->getRepository($type);
		$field = $this->managers[$type]->getClassMetadata($type)->getSingleIdentifierFieldName();
		if ($entity->$field === NULL) {
			throw new InvalidArgumentException("Missing value for identifier field '$field'.");
		}
		return (string) $entity->$field;
	}

	/**
	 * @param string $class
	 * @return EntityRepository|null
	 */
	private function getRepository($class)
	{
		if (!array_key_exists($class, $this->repositories)) {
			$manager = $this->managerRegistry->getManagerForClass($class);
			if ($manager) {
				$this->managers[$class] = $manager;
				$this->repositories[$class] = $manager->getRepository($class);
			} else {
				return;
			}
		}
		return $this->repositories[$class];
	}

}
