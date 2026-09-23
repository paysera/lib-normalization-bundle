# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.4.0
### Added
- Support for Symfony 7.4.

### Changed
- `Configuration::getConfigTreeBuilder()` declares the `TreeBuilder` return type, which Symfony 7 requires.
  Breaking for subclasses that override `getConfigTreeBuilder()` without a return type: add `: TreeBuilder` to the override.
- `PayseraNormalizationExtension` extends `Symfony\Component\DependencyInjection\Extension\Extension` instead of
  `Symfony\Component\HttpKernel\DependencyInjection\Extension` (internal since Symfony 7.1, deprecated in 8.1), and
  `load()` documents its `void` return. Together with the return type above, this removes the deprecation notices that
  Symfony 6.4 and 7.4 report for the bundle's own classes when their debug class loader is on.
- `symfony/config`, `symfony/dependency-injection` and `symfony/http-kernel`, which the bundle's code uses, are required
  explicitly, at the same versions as `symfony/framework-bundle`.

## 1.3.1
### Added
- `void` phpdoc typehint to `PayseraNormalizationBundle::build` method to fix the deprecation message on Symfony 6

## 1.3.0
### Added
- Support for Symfony 6

## 1.2.0
### Added
- Support for Symfony 5.4

## 1.1.0
### Added
- Support for PHP 8
- Support for `phpunit/phpunit` version `9.0`

### Removed
- `paysera/lib-php-cs-fixer-config`

## 1.0.0
### Added
- Improved requirements to stable versions

## [Unreleased]
### Added
- Support for normalization groups – all tags take optional attribute `group`;
- `paysera_normalization.normalizer_registry_provider` service for `NormalizerRegistryProviderInterface`
    instance;
- `CoreNormalizer` and `CoreDenormalizer` services registered with FQCN, so auto-wiring will work for them.

### Changed
- As `NormalizerRegistry` class was removed, type-hint `NormalizerRegistryInterface` when getting
`paysera_normalization.normalizer_registry` service;
- all services marked as private.


[Unreleased]: https://github.com/paysera/lib-normalization-bundle/compare/0.1.1...HEAD
