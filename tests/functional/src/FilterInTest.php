<?php

declare(strict_types=1);

namespace Tests\Functional;

use Arachne\Codeception\Module\NetteDIModule;
use Arachne\EntityLoader\EntityLoader;
use Codeception\Test\Unit;
use Tests\Functional\Fixtures\Article;
use Tests\Functional\Fixtures\ArticleQuery;

class FilterInTest extends Unit
{
    /**
     * @var NetteDIModule
     */
    protected $tester;

    public function testId()
    {
        $entityLoader = $this->tester->grabService(EntityLoader::class);
        $article = $entityLoader->filterIn(Article::class, 1);
        $this->assertInstanceOf(Article::class, $article);
        $this->assertSame($article->getId(), 1);
    }

    public function testQuery()
    {
        $entityLoader = $this->tester->grabService(EntityLoader::class);
        $article = $entityLoader->filterIn(Article::class, new ArticleQuery());
        $this->assertInstanceOf(Article::class, $article);
        $this->assertSame($article->getId(), 1);
    }

    /**
     * @expectedException \Nette\Application\BadRequestException
     * @expectedExceptionMessage Desired entity of type "Tests\Functional\Fixtures\Article" could not be found.
     */
    public function testError()
    {
        $entityLoader = $this->tester->grabService(EntityLoader::class);
        $entityLoader->filterIn(Article::class, 2);
    }
}
