<?php

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Arachne\EntityLoader\Entity;
use Arachne\EntityLoader\IConverter;
use Doctrine\ORM\EntityRepository;
use Nette\Application\BadRequestException;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class DoctrineConverter extends Object implements IConverter
{

	/** @var ManagerRegistry */
	protected $managerRegistry;

	/** @var EntityRepository[] */
	private $repositories;

	/**
	 * @param ManagerRegistry
	 */
	public function __construct(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->repositories = array();
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return object
	 * @throws BadRequestException
	 */
	public function parameterToEntity($type, $value)
	{
		$entity = $this->getRepository($type)->findOneBy(array('id' => $value));
		if (!$entity) {
			throw new BadRequestException("Desired entity of type '$type' could not be found.");
		}
		return $entity;
	}

	/**
	 * @param string $type
	 * @param object $entity
	 * @return string
	 */
	public function entityToParameter($type, $entity)
	{
		if (!isset($entity->id) || $entity->id === NULL || !$entity instanceof $type) {
			throw new InvalidArgumentException('Entity is not instance of the class given in annotation or the column \'id\' is not specified.');
		}
		return (string) $entity->id;
	}

	/**
	 * @param string $class
	 * @return EntityRepository
	 */
	private function getRepository($class)
	{
		if (!array_key_exists($class, $this->repositories)) {
			$this->repositories[$class] = $this->managerRegistry->getManagerForClass($class)->getRepository($class);
		}
        return $this->repositories[$class];
	}

}
