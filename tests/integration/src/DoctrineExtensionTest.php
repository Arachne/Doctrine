<?php

declare(strict_types=1);

namespace Tests\Integration;

use Codeception\Test\Unit;
use Contributte\Codeception\Module\NetteDIModule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\ResolvedFormType;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DoctrineExtensionTest extends Unit
{
    /**
     * @var NetteDIModule
     */
    protected $tester;

    public function testUniqueConstraintValidator(): void
    {
        $this->tester->useConfigFiles(['config/validator.neon']);
        $factory = $this->tester->grabService(ConstraintValidatorFactoryInterface::class);
        self::assertInstanceOf(UniqueEntityValidator::class, $factory->getInstance(new UniqueEntity(['fields' => ['id']])));
    }

    public function testEntityType(): void
    {
        $this->tester->useConfigFiles(['config/forms.neon']);
        $registry = $this->tester->grabService(FormRegistryInterface::class);
        self::assertTrue($registry->hasType(EntityType::class));
        $type = $registry->getType(EntityType::class);
        self::assertInstanceOf(ResolvedFormType::class, $type);
        self::assertInstanceOf(EntityType::class, $type->getInnerType());
    }
}
