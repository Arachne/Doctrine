<?php

namespace Tests\Integration\Classes;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette\Object;

/**
 * @ORM\Entity
 */
class Page extends Object
{

	use Identifier;

	/**
	 * @ORM\OneToOne( targetEntity="Article", inversedBy="page" )
	 * @var Article
	 */
	private $article;

	public function getArticle()
	{
		return $this->article;
	}

}
