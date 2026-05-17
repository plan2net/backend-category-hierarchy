# Backend Category Hierarchy

[![Packagist Version](https://img.shields.io/packagist/v/plan2net/backend-category-hierarchy.svg)](https://packagist.org/packages/plan2net/backend-category-hierarchy)
[![Downloads](https://img.shields.io/packagist/dt/plan2net/backend-category-hierarchy.svg)](https://packagist.org/packages/plan2net/backend-category-hierarchy)
[![Supported TYPO3](https://img.shields.io/badge/TYPO3-13.4%20%7C%2014-orange.svg)](https://get.typo3.org/)
[![Supported PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3%20%7C%208.4-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](LICENSE)

Show the full parent path of every `sys_category` record in TYPO3 backend lists.

![Backend list view of categories](Documentation/category_list_default.png)

The hierarchy is also shown in the global backend search:

![Hierarchy in the global backend search](Documentation/live_search_results.png)

## Why

TYPO3's backend lists only show the leaf title of a category. Once an editor
has multiple branches with categories named _News_, _Frontend_, or _Events_,
those rows become indistinguishable.

This extension augments the displayed title with its full ancestor chain in
list views and reference dropdowns. Edit forms and tree views are left
untouched.

## Installation

```bash
composer require plan2net/backend-category-hierarchy
```

## Configuration

By default, a category record like _Java_ under _Programming_ under _Topic_
renders as:

```
Java (Programming > Topic)
```

To change the format per Site, add a `backendCategoryHierarchy` block to
`config/sites/<identifier>/config.yaml`:

```yaml
backendCategoryHierarchy:
  titleTemplate: '{ancestors} > {current}'
  ancestorSeparator: ' > '
```

With the snippet above, the same category renders as:

```
Programming > Topic > Java
```

Placeholders in `titleTemplate`:

- `{current}` — the category title
- `{ancestors}` — the ancestor chain joined by `ancestorSeparator`

Invalid values (empty, or a template missing one of the placeholders) silently
fall back to the defaults `{current} ({ancestors})` and ` > `.

## Development

```bash
composer test:unit   # phpunit
composer quality     # php-cs-fixer + phpstan + psalm + phpunit
```
