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

namespace ApiExtension\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionTransformer implements TransformerInterface, TransformerAwareInterface
{
    use TransformerAwareTrait;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function supports(array $mapping, $value): bool
    {
        return null !== $mapping['targetEntity']
            && $this->registry->getManagerForClass($mapping['targetEntity'])->getClassMetadata($mapping['targetEntity']) instanceof ClassMetadataInfo
            && \in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY], true);
    }

    public function toObject(array $mapping, $value): Collection
    {
        if (is_a($value, Collection::class)) {
            return $value;
        }

        if (\is_array($value)) {
            return new ArrayCollection($value);
        }

        $values = array_values(array_map([$this, 'clean'], explode(',', $value)));
        $className = $mapping['targetEntity'];
        $em = $this->registry->getManagerForClass($className);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository($className)->createQueryBuilder('o');
        $classMetadata = $em->getClassMetadata($className);
        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if ('string' === $classMetadata->getTypeOfField($fieldName)) {
                $queryBuilder->orWhere($queryBuilder->expr()->in("o.$fieldName", ':query'));
                $queryBuilder->setParameter('query', $values);
            }
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    public function toScalar(array $mapping, $values): array
    {
        if (!$values instanceof Collection && !\is_array($values)) {
            $values = $this->toObject($mapping, $values);
        }
        if ($values instanceof Collection) {
            $values = $values->getValues();
        }

        foreach ($values as $key => $value) {
            $values[$key] = $this->transformer->toScalar(['type' => ClassMetadataInfo::ONE_TO_ONE] + $mapping, $value);
        }

        return $values;
    }

    private function clean($value)
    {
        $value = trim((string) $value);
        if (empty($value)) {
            return '';
        }
        if (!preg_match('/[^0-9.]+/', $value)) {
            return preg_match('/[.]+/', $value) ? (float) $value : (int) $value;
        }
        if ('true' === $value) {
            return true;
        }
        if ('false' === $value) {
            return false;
        }

        return (string) $value;
    }
}
