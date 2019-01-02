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

namespace ApiExtension\Populator\Guesser;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class CollectionGuesser implements GuesserInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function supports(array $mapping): bool
    {
        return null !== $mapping['targetEntity']
            && $this->registry->getManagerForClass($mapping['targetEntity'])->getClassMetadata($mapping['targetEntity']) instanceof ClassMetadataInfo
            && \in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY], true);
    }

    public function getValue(array $mapping): array
    {
        return $this->registry->getManagerForClass($mapping['targetEntity'])->getRepository($mapping['targetEntity'])->findBy([], null, mt_rand(3, 10));
    }
}
