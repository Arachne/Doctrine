<?php

namespace Arachne\Doctrine\EntityLoader;

use Arachne\DI\Resolver\ResolverInterface;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ConverterResolver extends Object implements ResolverInterface
{

	/** @var callable */
	private $resolver;
	
	/** @var DoctrineConverter */
	private $converter;

	public function __construct(callable $resolver, DoctrineConverter $converter)
	{
		$this->resolver = $resolver;
		$this->converter = $converter;
	}

	/**
	 * @param string $name
	 * @return object
	 */
	public function __invoke($name)
	{
		return $this->resolver->__invoke($name) ?: ($this->converter->entityExists($name) ? $this->converter : NULL);
	}

}
