<?php

namespace Tests\Integration\Classes;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette\Object;

/**
 * @ORM\Entity
 */
class Article extends Object
{

	use Identifier;

	/**
	 * @ORM\Column
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\OneToOne( targetEntity="Page", mappedBy="article" )
	 * @var Page
	 */
	private $page;

}
