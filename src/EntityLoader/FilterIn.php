<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Arachne\EntityLoader\FilterInInterface;
use Doctrine\ORM\EntityRepository;
use Nette\Application\BadRequestException;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterIn extends Object implements FilterInInterface
{

	/** @var EntityRepository */
	private $repository;

	/**
	 * @param EntityRepository $repository
	 */
	public function __construct(EntityRepository $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * @param mixed $value
	 * @return object
	 * @throws BadRequestException
	 */
	public function filterIn($value)
	{
		if (is_array($value)) {
			$entity = $this->repository->findOneBy($value);
		} elseif (!is_object($value)) {
			$entity = $this->repository->find($value);
		} elseif ($value instanceof QueryInterface) {
			$entity = $value->getEntity($this->repository);
		}
		$class = $this->repository->getClassName();
		if (!$entity instanceof $class) {
			throw new BadRequestException('Desired entity of type \'' . $this->repository->getClassName() . '\' could not be found.');
		}
		return $entity;
	}

}
