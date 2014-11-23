<?php

namespace Arachne\Doctrine\EntityLoader;

use Arachne\DI\Resolver\ResolverFactoryInterface;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ResolverFactory extends Object implements ResolverFactoryInterface
{

	/** @var ResolverFactoryInterface */
	private $resolverFactory;
	
	/** @var DoctrineConverter */
	private $converter;

	public function __construct(ResolverFactoryInterface $resolverFactory, DoctrineConverter $converter)
	{
		$this->resolverFactory = $resolverFactory;
		$this->converter = $converter;
	}

	/**
	 * @return callable
	 */	 	
	public function create()
	{
		$resolver = $this->resolverFactory->create();
		return function ($name) use ($resolver) {
			return $resolver($name) ?: ($this->converter->entityExists($name) ? $this->converter : NULL);
		};
	}

}
