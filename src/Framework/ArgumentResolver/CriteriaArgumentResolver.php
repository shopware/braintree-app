<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\ArgumentResolver;

use Swag\Braintree\Framework\ArgumentResolver\Dto\Criteria;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

#[AutoconfigureTag(name: 'controller.argument_value_resolver')]
class CriteriaArgumentResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @return iterable<Criteria>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$argument->getType() || !\is_a($argument->getType(), Criteria::class, true)) {
            return [];
        }

        yield $this->serializer->deserialize(
            $request->getContent(),
            Criteria::class,
            'json',
        );
    }
}
