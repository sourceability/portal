# sourceability/portal

A CLI and PHP Library that helps getting structured data out from GPT.

Given a [JSON Schema][json_schema], GPT is [perfectly capable of outputting JSON that conforms to the schema][blog_gpt_json_schema].
This approach enables GPT to be used programmatically for non-conversational use cases.

For example, before parsing a user uploaded CSV, you could ask GPT to map its headers to the ones your code supports:
```
$ bin/portal ./examples/csv_headers.yaml '{
  "supportedHeaders":["firstName","age"], 
  "headers":["Prenom","Nom Famille","Annees"]}'
...

Completion Results:
===================

{
    "mappedHeaders": {
        "Prenom": "firstName",
        "Nom Famille": null,
        "Annees": "age"
    }
}
```

## Trying out

```
git clone https://github.com/sourceability/portal.git
cd portal
make php
bin/portal ./examples/csv_headers.yaml
```

## Installation

```
composer require sourceability/portal
```

[json_schema]: https://json-schema.org
[blog_gpt_json_schema]: https://blog.humphd.org/pouring-language-through-shape/
