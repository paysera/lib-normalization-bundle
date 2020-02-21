# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
