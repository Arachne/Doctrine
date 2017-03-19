<?php

declare(strict_types=1);

namespace Tests\Functional;

use Arachne\Codeception\Module\NetteDIModule;
use Arachne\Doctrine\Exception\EntityValidationException;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManager;
use Tests\Functional\Fixtures\Article;

class ValidatorListenerTest extends Unit
{
    /**
     * @var NetteDIModule
     */
    protected $tester;

    public function testFlushInsertSuccess(): void
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('FooBar');
        $em->persist($article);
        $em->flush();
    }

    public function testFlushInsertException(): void
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('');
        $em->persist($article);
        try {
            $em->flush();
            self::fail();
        } catch (EntityValidationException $e) {
            self::assertRegExp('~^Entity "Tests\\\\Functional\\\\Fixtures\\\\Article@[a-z0-9]++" is not valid: Object\\(Tests\\\\Functional\\\\Fixtures\\\\Article\\)\\.name:\\s++This value should not be blank\\. \\(code [a-z0-9-]++\\)$~', $e->getMessage());
        }
    }

    public function testFlushUpdateSuccess(): void
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('FooBar');
        $em->persist($article);
        $em->flush();
        $article->setName('BarFoo');
        $em->flush();
    }

    public function testFlushUpdateException(): void
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('FooBar');
        $em->persist($article);
        $em->flush();
        $article->setName('');
        try {
            $em->flush();
            self::fail();
        } catch (EntityValidationException $e) {
            self::assertRegExp('~^Entity "Tests\\\\Functional\\\\Fixtures\\\\Article@[a-z0-9]++" is not valid: Object\\(Tests\\\\Functional\\\\Fixtures\\\\Article\\)\\.name:\\s++This value should not be blank\\. \\(code [a-z0-9-]++\\)$~', $e->getMessage());
        }
    }
}
