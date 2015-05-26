<?php

namespace Tests\Integration;

use Arachne\EntityLoader\EntityUnloader;
use Codeception\TestCase\Test;
use Tests\Integration\Classes\Article;
use Tests\Integration\Classes\Page;

class FilterOutTest extends Test
{

	public function testId()
	{
		$em = $this->guy->grabService(\Doctrine\ORM\EntityManagerInterface::class);
		$article = $em->find(Article::class, 1);
		$this->assertInstanceOf(Article::class, $article);
		$entityUnloader = $this->guy->grabService(EntityUnloader::class);
		$id = $entityUnloader->filterOut($article);
		$this->assertSame($id, '1');
	}

	public function testProxy()
	{
		$em = $this->guy->grabService(\Doctrine\ORM\EntityManagerInterface::class);
		$page = $em->createQueryBuilder()
			->select('p')
			->from(Page::class, 'p')
			->where('p.id = :id')
			->setParameter('id', 1)
			->getQuery()
			->getSingleResult();
		$article = $page->getArticle();
		$this->assertInstanceOf(Article::class, $article);
		$this->assertNotEquals(Article::class, get_class($article));
		$entityUnloader = $this->guy->grabService(EntityUnloader::class);
		$id = $entityUnloader->filterOut($article);
		$this->assertSame($id, '1');
	}

	/**
	 * @expectedException Arachne\Doctrine\Exception\InvalidArgumentException
	 * @expectedExceptionMessage Missing value for identifier field 'id'.
	 */
	public function testError()
	{
		$entityUnloader = $this->guy->grabService(EntityUnloader::class);
		$entityUnloader->filterOut(new Article());
	}

}