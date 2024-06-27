<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Serializer;

use Swag\Braintree\Framework\Serializer\BraintreeNormalizer;
use Swag\Braintree\Framework\Serializer\CollectionNormalizer;
use Swag\Braintree\Framework\Serializer\EntityNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;

class TestSerializer
{
    public static function create(): Serializer
    {
        $objectNormalizer = new ObjectNormalizer(new ClassMetadataFactory(new AttributeLoader()));

        return new Serializer([
            new UidNormalizer(),
            $objectNormalizer,
            new EntityNormalizer($objectNormalizer),
            new BraintreeNormalizer(),
            new CollectionNormalizer(),
            new ArrayDenormalizer(),
            new ProblemNormalizer(),
            new PropertyNormalizer(),
        ], [
            new JsonEncoder(),
        ]);
    }
}
