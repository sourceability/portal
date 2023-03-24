<?php

declare(strict_types=1);

namespace Sourceability\Portal\ApiPlatformDemo;

use ApiPlatform\JsonSchema\SchemaFactory;
use ApiPlatform\JsonSchema\TypeFactory;
use ApiPlatform\Metadata\Property\Factory\AttributePropertyMetadataFactory;
use ApiPlatform\Metadata\Property\Factory\DefaultPropertyMetadataFactory;
use ApiPlatform\Metadata\Property\Factory\PropertyInfoPropertyMetadataFactory;
use ApiPlatform\Metadata\Property\Factory\PropertyInfoPropertyNameCollectionFactory;
use ApiPlatform\Metadata\Resource\Factory\AttributesResourceMetadataCollectionFactory;
use ApiPlatform\Metadata\Resource\Factory\PhpDocResourceMetadataCollectionFactory;
use Sourceability\Portal\Spell\ApiPlatformSpell as BaseSpell;
use Sourceability\Portal\Spell\SpellFactory;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class ApiPlatformSpell extends BaseSpell implements SpellFactory
{
    public static function create(): static
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $propertyInfo = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpDocExtractor, $reflectionExtractor],
            [$phpDocExtractor],
            [$reflectionExtractor],
            [$reflectionExtractor]
        );

        $schemaFactory = new SchemaFactory(
            new TypeFactory(null),
            new PhpDocResourceMetadataCollectionFactory(
                new AttributesResourceMetadataCollectionFactory(

                )
            ),
            new PropertyInfoPropertyNameCollectionFactory(
                $propertyInfo
            ),
            new DefaultPropertyMetadataFactory(
                new AttributePropertyMetadataFactory(
                    new PropertyInfoPropertyMetadataFactory(
                        $propertyInfo
                    )
                )
            )
        );

        $serializer = new Serializer([
            new ObjectNormalizer(),
            new ArrayDenormalizer(),
        ], [
            new JsonEncoder(),
        ]);

        return new static(
            $schemaFactory,
            $serializer
        );
    }
}
