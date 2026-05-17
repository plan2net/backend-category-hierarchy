# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [14.0.0] - 2026-05-17

### Added

- TYPO3 v14 LTS support alongside v13.4 LTS.
- Site Set with sensible defaults. Activate per Site by adding
  `plan2net/backend-category-hierarchy` to the `dependencies:` list in
  `config/sites/<id>/config.yaml`. No further configuration required to use
  the defaults.
- Configurable per Site via the `settings:` block:
  `backendCategoryHierarchy.titleTemplate`,
  `backendCategoryHierarchy.ancestorSeparator`, and
  `backendCategoryHierarchy.compactTitleTemplate`. Placeholders: `{current}`,
  `{ancestors}`.
- `compactTitleTemplate` (default `{current} ({ancestors})`) used in the
  global backend search so the category name stays readable when long titles
  get truncated.
- English and German translations for the settings labels (XLIFF).

### Changed

- Minimum TYPO3 is v13.4 LTS (was v13.2).
- Minimum PHP is 8.2 (to match TYPO3 v14's floor).

### Fixed

- Ancestor titles fall back to the default-language title when no translation
  exists in the active language. Previously, untranslated ancestors were
  silently dropped, leaving holes in the chain.
- Chain resolves correctly when a record's `parent` points at a translation
  row instead of the default-language record (uses `l10n_parent` to normalise).

[Unreleased]: https://github.com/plan2net/backend-category-hierarchy/compare/14.0.0...HEAD
[14.0.0]: https://github.com/plan2net/backend-category-hierarchy/releases/tag/14.0.0
