<?php

namespace Arachne\Doctrine\EntityLoader;

use AppendIterator;
use ArrayIterator;
use Arachne\DIHelpers\ResolverInterface;
use Iterator;
use IteratorAggregate;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ConverterResolver extends Object implements IteratorAggregate, ResolverInterface
{

	/** @var ResolverInterface */
	private $resolver;

	/** @var DoctrineConverter */
	private $converter;

	public function __construct(ResolverInterface $resolver, DoctrineConverter $converter)
	{
		$this->resolver = $resolver;
		$this->converter = $converter;
	}

	/**
	 * @param string $name
	 * @return object
	 */
	public function resolve($name)
	{
		return $this->resolver->resolve($name) ?: ($this->converter->entityExists($name) ? $this->converter : NULL);
	}

	/**
	 * @return Iterator
	 */
	public function getIterator()
	{
		$iterator = new AppendIterator();
		$iterator->append($this->resolver->getIterator());
		$iterator->append(new ArrayIterator([ $this->converter ]));
		return $iterator;
	}

}
