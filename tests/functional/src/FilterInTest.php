<?php

declare(strict_types=1);

namespace Tests\Functional;

use Arachne\EntityLoader\EntityLoader;
use Nette\Application\BadRequestException;
use Tests\Functional\Fixtures\Article;
use Tests\Functional\Fixtures\ArticleQuery;

class FilterInTest extends DatabaseTest
{
    public function testId(): void
    {
        $entityLoader = $this->tester->grabService(EntityLoader::class);
        $article = $entityLoader->filterIn(Article::class, 1);
        $this->assertInstanceOf(Article::class, $article);
        $this->assertSame($article->getId(), 1);
    }

    public function testQuery(): void
    {
        $entityLoader = $this->tester->grabService(EntityLoader::class);
        $article = $entityLoader->filterIn(Article::class, new ArticleQuery());
        $this->assertInstanceOf(Article::class, $article);
        $this->assertSame($article->getId(), 1);
    }

    public function testError(): void
    {
        $entityLoader = $this->tester->grabService(EntityLoader::class);
        try {
            $entityLoader->filterIn(Article::class, 2);
            self::fail();
        } catch (BadRequestException $e) {
            self::assertSame('Desired entity of type "Tests\Functional\Fixtures\Article" could not be found.', $e->getMessage());
        }
    }
}
