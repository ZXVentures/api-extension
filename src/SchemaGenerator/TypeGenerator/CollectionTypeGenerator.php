<?php

/*
 * This file is part of the API Extension project.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\SchemaGenerator\TypeGenerator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Container\ContainerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionTypeGenerator implements TypeGeneratorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function supports(array $mapping, array $context = []): bool
    {
        return null !== $mapping['targetEntity']
            && $this->registry->getManagerForClass($mapping['targetEntity'])->getClassMetadata($mapping['targetEntity']) instanceof ClassMetadataInfo
            && \in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY], true);
    }

    public function generate(array $mapping, array $context = []): array
    {
        return [
            'type' => 'array',
            'items' => $this->container->get('schemaGenerator')->generate(new \ReflectionClass($mapping['targetEntity']), $context),
        ];
    }
}
