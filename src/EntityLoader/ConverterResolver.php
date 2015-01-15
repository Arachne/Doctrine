<?php

namespace Arachne\Doctrine\EntityLoader;

use Arachne\DIHelpers\ResolverInterface;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ConverterResolver extends Object implements ResolverInterface
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
	public function __invoke($name)
	{
		return $this->resolver->resolve($name) ?: ($this->converter->entityExists($name) ? $this->converter : NULL);
	}

}
