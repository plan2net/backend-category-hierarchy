# Backend category hierarchy view

## About

This extension adds a small method to process the category title and add the whole hierarchy to the list view.

![Backend list view of categories](Documentation/category_list_default.png)

It does not change anything in the record edit or tree views.

## Compatibility

Compatible with TYPO3 v13 LTS and v14 LTS.

## Configuration

By default, a category record like _Java_ under _Programming_ under _Topic_ renders as:

```
Java (Programming > Topic)
```

To override the format per Site, add a `backendCategoryHierarchy` block to
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

## Tests

```bash
composer test:unit
```
