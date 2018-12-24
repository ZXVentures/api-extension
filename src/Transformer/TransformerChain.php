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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TransformerChain implements TransformerInterface
{
    /**
     * @var TransformerInterface[]
     */
    private $transformers;

    public function __construct(array $transformers)
    {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof TransformerAwareInterface) {
                $transformer->setTransformer($this);
            }
        }
        $this->transformers = $transformers;
    }

    public function toObject(array $context, $value)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($context, $value)) {
                return $transformer->toObject($context, $value);
            }
        }

        return $value;
    }

    public function toScalar(array $context, $value)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($context, $value)) {
                return $transformer->toScalar($context, $value);
            }
        }

        return $value;
    }

    public function supports(array $context, $value): bool
    {
        return true;
    }
}
