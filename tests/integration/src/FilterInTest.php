<?php

namespace Tests\Integration;

use Arachne\EntityLoader\EntityLoader;
use Codeception\TestCase\Test;
use Tests\Integration\Classes\Article;
use Tests\Integration\Classes\ArticleQuery;

class FilterInTest extends Test
{

	public function testId()
	{
		$entityLoader = $this->guy->grabService(EntityLoader::class);
		$article = $entityLoader->filterIn(Article::class, 1);
		$this->assertInstanceOf(Article::class, $article);
		$this->assertSame($article->id, 1);
	}

	public function testArray()
	{
		$entityLoader = $this->guy->grabService(EntityLoader::class);
		$article = $entityLoader->filterIn(Article::class, [
			'name' => 'Lorem Ipsum',
		]);
		$this->assertInstanceOf(Article::class, $article);
		$this->assertSame($article->id, 1);
	}

	public function testQuery()
	{
		$entityLoader = $this->guy->grabService(EntityLoader::class);
		$article = $entityLoader->filterIn(Article::class, new ArticleQuery());
		$this->assertInstanceOf(Article::class, $article);
		$this->assertSame($article->id, 1);
	}

	/**
	 * @expectedException Nette\Application\BadRequestException
	 * @expectedExceptionMessage Desired entity of type 'Tests\Integration\Classes\Article' could not be found.
	 */
	public function testError()
	{
		$entityLoader = $this->guy->grabService(EntityLoader::class);
		$entityLoader->filterIn(Article::class, 2);
	}

}
