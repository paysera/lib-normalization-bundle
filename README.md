# Paysera Normalization Bundle

[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-packagist]
[![Latest Stable Version][ico-version]][link-packagist]
[![PHP Version Require][ico-php]][link-packagist]
[![License][ico-license]](LICENSE)

This bundle allows to de/normalize your business entities (plain PHP objects)
without tightly coupling them with your normalization format. You would usually do this
before converting normalized structure to JSON or after converting from it.

## Why?

Symfony has Serializer component that has normalizers as a part of it.
This component is created for similar reasons but with different approach.

Symfony component exposes your business entities by default, but allows sophisticated but
challenging configuration options. It also writing custom normalization logic, but it usually
resides inside your normalized classes (which probably are plain PHP objects).

Paysera Normalization library embraces simplicity by always writing a bit of code
for getting full control of the situation – normalization logic is placed in related classes,
which are usually registered from DIC.
This allows to use other services, fetch data from database, call remote services if needed or
make any other things in familiar PHP source code. You can easily rename any fields, use any custom
naming, duplicate some data for backward compatibility or, well, just write any other code.
No difficult configuration is needed for edge-cases, as you have full control over the situation.

Main features of this bundle:
- supports explicit type safety when denormalizing by integrating 
[lib-object-wrapper](https://github.com/paysera/lib-object-wrapper);
- normalization type can be guessed by passed data;
- easily reuse other de/normalizers without direct dependencies;
- supports different normalization groups with fallback to default one;
- supports explicitly or implicitly included fields, allowing performance tuning in normalization process.

## Installation

```bash
composer require paysera/lib-normalization-bundle
```

## Configuration

```yaml
paysera_normalization:
  register_normalizers:
    date_time:      # registers de/normalizers for DateTime, DateTimeImmutable and DateTimeInterface
      format: "U"   # Unix timestamp. Use any from https://www.php.net/manual/en/function.date.php
```

## Basic usage

Write de/normalizers for your business entities:

```php
<?php

// ...

class ContactDetailsNormalizer implements NormalizerInterface, ObjectDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return ContactDetails::class;
    }

    /**
     * @param ContactDetails $data
     * @param NormalizationContext $normalizationContext
     *
     * @return array
     */
    public function normalize($data, NormalizationContext $normalizationContext)
    {
        return [
            'email' => $data->getEmail(),
            // will automatically follow-up with normalization by guessed types:
            'residence_address' => $data->getResidenceAddress(),
            'shipping_addesses' => $data->getShippingAddresses(),
        ];
    }

    public function denormalize(ObjectWrapper $data, DenormalizationContext $context)
    {
        return (new ContactDetails())
            ->setEmail($data->getRequiredString('email'))
            ->setResidenceAddress(
                $context->denormalize($data->getRequiredObject('residence_address'), Address::class)
            )
            ->setShippingAddresses(
                $context->denormalizeArray($data->getArrayOfObject('shipping_addesses'), Address::class)
            )
        ;
    }
}
```

```php
<?php

// ...

class AddressNormalizer implements NormalizerInterface, ObjectDenormalizerInterface, TypeAwareInterface
{
    private $countryRepository;
    private $addressBuilder;

    // ...

    public function getType(): string
    {
        return Address::class;
    }

    /**
     * @param Address $data
     * @param NormalizationContext $normalizationContext
     *
     * @return array
     */
    public function normalize($data, NormalizationContext $normalizationContext)
    {
        return [
            'country_code' => $data->getCountry()->getCode(),
            'city' => $data->getCity(),
            'full_address' => $this->addressBuilder->buildAsText($data->getStreetData()),
        ];
    }

    public function denormalize(ObjectWrapper $data, DenormalizationContext $context)
    {
        $code = $data->getRequiredString('country_code');
        $country = $this->countryRepository->findOneByCode($code);
        if ($country === null) {
            throw new InvalidDataException(sprintf('Unknown country %s', $code));
        }   

        return (new Address())
            ->setCountry($country)
            ->setCity($data->getRequiredString('city'))
            ->setStreetData(
                $this->addressBuilder->parseFromText($data->getRequiredString('full_address'))
            )
        ;
    }
}
```

If you don't use auto-configuration
(also keep in mind that it works only when you implement `TypeAwareInterface`), tag your services:

```xml
<services>
    <service id="ContactDetailsNormalizer">
        <tag name="paysera_normalization.normalizer"/>
        <tag name="paysera_normalization.object_denormalizer"/>
    </service>

    <service id="AddressNormalizer">
        <!-- you can also use just this tag when you implement TypeAwareInterface -->
        <tag name="paysera_normalization.autoconfigured_normalizer"/>
    </service>
</services>
```

Use for de/normalization:

```php

// inject $coreDenormalizer as paysera_normalization.core_denormalizer
// FQCN also works as service ID, so autowiring should work if you use it

// must be stdClass, not array
$data = json_decode('{
    "email":"a@example.com",
    "residence_address":{"country_code":"LT","city":"Vilnius","full_address":"Park street 182b-12"},
    "shipping_addresses":[]
}');
$contactDetails = $coreDenormalizer->denormalize($data, ContactDetails::class);


// inject $coreNormalizer as paysera_normalization.core_normalizer
// FQCN also works as service ID, so autowiring should work if you use it

$normalized = $coreNormalizer->normalize($contactDetails);

var_dump($normalized);
// object(stdClass)#1 (3) { ...
```

## Advanced usage

### Available tags

| Tag                                               | Description                                           | Available attributes | Required interface               |
|---------------------------------------------------|-------------------------------------------------------|----------------------|----------------------------------|
| `paysera_normalization.normalizer`                | Registers service as normalizer                       | `type`, `group`      | `NormalizerInterface`            |
| `paysera_normalization.object_denormalizer`       | Registers service as object denormalizer              | `type`, `group`      | `ObjectDenormalizerInterface`    |
| `paysera_normalization.mixed_type_denormalizer`   | Registers service as mixed type denormalizer          | `type`, `group`      | `MixedTypeDenormalizerInterface` |
| `paysera_normalization.autoconfigured_normalizer` | Registers service depending on implemented interfaces | `group`              | `TypeAwareInterface`             |

If service does not implement `TypeAwareInterface` interface, `type` attribute is required. You can provide it in
any case to overwrite any value returned from `getType` method.

`group` attribute instructs to register de/normalizer to specific normalization group instead of default one. See usage
on normalization groups below for more information.

You can use several (even same) tags on a service. For example:

```xml
<services>
    <service id="ContactDetailsNormalizer">
        <!-- Register as normalizer, use type returned from getType() -->
        <tag name="paysera_normalization.normalizer"/>
        <!-- Register with additional type -->
        <tag name="paysera_normalization.normalizer" type="contact_details"/>
        <!-- Register as object denormalizer, use type returned from getType() -->
        <tag name="paysera_normalization.object_denormalizer"/>
        <!-- Also register for normalization group "v2" -->
        <tag name="paysera_normalization.object_denormalizer" group="v2"/>
    </service>
</services>
```

### Normalizing data

Normalization is a process of converting your business objects to "normalized" (plain) structures.

This can be done when returning them as response to REST requests, before sending to some MQ system, before
storing to any relational or NoSQL database or in any other case where you need plain, manageable representation.

Normalization is initiated by using `CoreNormalizer` (`paysera_normalization.core_normalizer` service) `normalize`
method which has the following interface:
```
public function normalize($data, string $type = null, NormalizationContext $context = null)
```

If `$type` is not passed, code tries to find registered normalizer in this order:
- for scalar values, same value is returned;
- for arrays, it's values are mapped by recursively guessing their normalizer types;
- for objects, normalizers with the following types (in this order) are looked for:
  - fully qualified class name of the object;
  - all parent classes of the object;
  - all implemented interfaces of the object;
  - if object implements `Traversable`, it's treated same as an array;
- `NormalizerNotFoundException` is thrown if type is not resolved.

With `NormalizationContext` you can customize normalization group and included fields.
Keep in mind that `NormalizationContext` needs the same `CoreNormalizer` instance when constructing it – it's passed
down to concrete normalizer instances which needs easy way to recursively normalize internal structures.

```php
<?php

/* @var CoreNormalizer $coreNormalizer */

$context = new NormalizationContext($coreNormalizer, ['*', 'user.address'], 'custom_group');

$normalized = $coreNormalizer->normalize($order, 'my_custom_type', $context);

$serialized = json_encode($normalized);
```

#### Included fields

You can configure an array of fields to be included in the normalized result.

`*` means all (default) fields of the object.

You can use `.` to indicate sub-elements, for example `user.address` or `user.*`.

Included fields are used in two separate places:
- Normalizers that support additional fields (not provided by default) or wants to provide some optimizations, should
manually check for field inclusions using passed `NormalizationContext`. See example below;
- after getting the normalized structure, it's filtered out leaving only the fields that were originally included.

```php
<?php

// ...

class ContactDetailsNormalizer implements NormalizerInterface
{
    /**
     * @param ContactDetails $data
     * @param NormalizationContext $normalizationContext
     *
     * @return array
     */
    public function normalize($data, NormalizationContext $normalizationContext)
    {
        return [
            'email' => $data->getEmail(),

            // this is only a possible optimization, as field would be still filtered out afterwards:
            'residence_address' => $normalizationContext->isFieldIncluded('residence_address')
                ? $data->getResidenceAddress()
                : null,

            // this is a field that will not be returned except if explicitly asked for:
            'shipping_addesses' => $normalizationContext->isFieldExplicitlyIncluded('shipping_addesses')
                ? $data->getShippingAddresses()
                : null,
        ];
    }
}
```

Using this structure, following table shows what structure would be returned with different field configurations.

| Included fields              | Returned structure                                                   |
|------------------------------|----------------------------------------------------------------------|
| Default                      | `{"email": ..., "residence_address": ...}`                           |
| `['*']`                      | `{"email": ..., "residence_address": ...}`                           |
| `['email']`                  | `{"email": ...}`                                                     |
| `['shipping_addesses']`      | `{"shipping_addesses": ...}`                                         |
| `['*', 'shipping_addesses']` | `{"email": ..., "residence_address": ..., "shipping_addesses": ...}` |

Usually optimizations make sense only when you make some remote calls to fetch the data or at least make any additional
database calls. In this example this can be the case if Doctrine did not load the data by relation beforehand.

### Denormalizing data

Denormalization is a process of converting "normalized" (plain) structures to your business objects.

This can be done when receiving JSON via some endpoint, getting the structure from MQ, database or in any other case
where you want to read the normalized structure.

Normalization is initiated by using `CoreDenormalizer` (`paysera_normalization.core_denormalizer` service) `denormalize`
method which has the following interface:
```
public function denormalize($data, string $type, DenormalizationContext $context = null)
```

Type is required here as there's nothing to guess it from.

You can configure normalization group to use with `DenormalizationContext`. Same as with normalization process,
make sure you pass the same `CoreDenormalizer` to your structured `DenormalizationContext`:

```php
<?php

$normalized = json_decode($serialized);

/* @var CoreDenormalizer $coreDenormalizer */

$context = new DenormalizationContext($coreDenormalizer, 'custom_group');

$order = $coreDenormalizer->denormalize($normalized, 'my_custom_type', $context);
```

Registered denormalizers have one of 2 interfaces (but never both):
- object denormalizer. It's used to denormalize only from (JSON) objects. They're passed `ObjectWrapper` instance as
a first argument;
- mixed type denormalizer. It can be used to denormalize from any structure – scalar types, arrays or objects.
In case of objects, plain `stdClass` instances are passed, they are not converted to `ObjectWrapper` instances.

### Normalization groups

Each de/normalizer can belong to some concrete normalization group. De/normalizers, registered without `group`
attribute, belong to default group – this means that they are always used as a fallback.

When using customised normalization group, de/normalizer is looked for in the following algorithm:
- looking for de/normalizer with the same group as provided;
- if not found – looking for de/normalizer with default group.

This allows to easily overwrite logic for concrete normalizers, but also have the default behavior in most common
use-cases.

## Semantic versioning

This bundle follows [semantic versioning](http://semver.org/spec/v2.0.0.html).

Public API of this bundle (in other words, you should only use these features if you want to easily update
to new versions):
- configuration of the bundle;
- only services documented in this readme;
- supported DIC tags, documented in this readme.

See [Symfony BC rules](https://symfony.com/doc/current/contributing/code/bc.html) for basic information
about what can be changed and what not in the API.

## Running tests

```
composer update
composer test
```

## Contributing

Feel free to create issues and give pull requests.

You can fix any code style issues using this command:
```
composer fix-cs
```

[ico-build]: https://github.com/paysera/lib-normalization-bundle/workflows/CI/badge.svg
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/paysera/lib-normalization-bundle.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/paysera/lib-normalization-bundle.svg
[ico-downloads]: https://img.shields.io/packagist/dt/paysera/lib-normalization-bundle.svg
[ico-version]: https://img.shields.io/packagist/v/paysera/lib-normalization-bundle.svg
[ico-php]: https://img.shields.io/packagist/dependency-v/paysera/lib-normalization-bundle/php
[ico-license]: https://img.shields.io/github/license/paysera/lib-normalization-bundle?color=blue

[link-build]: https://github.com/paysera/lib-normalization-bundle/actions
[link-scrutinizer]: https://scrutinizer-ci.com/g/paysera/lib-normalization-bundle/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/paysera/lib-normalization-bundle
[link-packagist]: https://packagist.org/packages/paysera/lib-normalization-bundle
