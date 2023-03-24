# sourceability/portal

<p align="center">
<img src="https://user-images.githubusercontent.com/611271/227591267-815bc626-5a78-4332-9129-11b341b6d4ae.png" width="150" />
</p>

A CLI and PHP Library that helps getting structured data out from GPT.

Given a [JSON Schema][json_schema], GPT is [perfectly capable of outputting JSON that conforms to the schema][blog_gpt_json_schema].
This approach enables GPT to be used programmatically for non-conversational use cases.

For example, before parsing a user uploaded CSV, you could ask GPT to map its headers to the ones your code supports:
```bash
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

## Static YAML

You can invoke portal with the path to a `.yaml` with the following format:
```yaml
schema:
    properties:
        barbar:
            type: string
examples:
    - foobar: hello
    - foobar: world
prompt: |
    Do something.
  
    {{ foobar }}
```

```
vendor/bin/portal my_spell.yaml
```

## Spell

The [Spell][Spell.php] interface is the main way to interact with this library.

```php
use Sourceability\Portal\Spell\Spell;

/*
 * @implements Spell<TInput, TOutput>
 */
class MySpell implements Spell
```
A spell is defined by its Input/Output types TInput and TOutput.
So for example, a spell that accepts a number and returns an array of string, would use `Spell<int, string<string>>`.

The `getExamples` method returns 0 or many inputs examples. This is very useful when iterating on a prompt.
```php
/**
 * @return array<TInput>
 */
public function getExamples(): array;
```

With the `getSchema` you return a JSON Schema:
```php
/**
 * @return string|array<string, mixed>|JsonSerializable The JSON-Schema of the desired completion output.
 */
public function getSchema(): string|array|JsonSerializable;
```

Make sure to leverage the [description][json_schema_description] and [examples][json_schema_examples] properties to give GPT more context and instructions:
```php
public function getSchema()
{
    return [
        'properties' => [
            'release' => [
                'description' => 'The release reference/key.',
                'examples' => ['v1.0.1', 'rc3', '2022.48.2'],
            ]
        ],
    ];
}
```

The `getPrompt` method is where you describe the desired behaviour:
```php
/**
 * @param TInput $input
 */
public function getPrompt($input): string
{
    return sprintf('Do something with ' . $input);
}
```

Finally, you can transform the json decoded GPT output into your output type:
```php
/**
 * @param array<mixed> $completionValue
 * @return array<TOutput>
 */
public function transcribe(array $completionValue): array
{
    return array_map(fn ($item) => new Money($item), $completionValue);
}
```

Once you've done all that, you can cast try your spell examples:
```
vendor/bin/portal 'App\Portal\FraudSpell'
```

Or invoke your spell with the PHP Api:
```php
$portal = new Portal(...);

$result = $portal->cast(
    new FraudSpell(),
    ['user' => $user->toArray()] // This contains TInput
);

// $result->value contains array<TOutput>
actOnThe($result->value);
```

## CLI

You can pass your own JSON example to the portal cli:
```
bin/portal spell.yaml '[{"hello":["worlds"]},{"hello":[]}]'
```

Use `-v`, `-vv`, `-vvv` to print more information like the prompts or the OpenAI API requests/responses.

## Examples

See [./examples/](./examples).

[json_schema]: https://json-schema.org
[json_schema_description]: https://www.learnjsonschema.com/2020-12/meta-data/description/
[json_schema_examples]: https://www.learnjsonschema.com/2020-12/meta-data/examples/
[blog_gpt_json_schema]: https://blog.humphd.org/pouring-language-through-shape/
[Spell.php]: src/Spell/Spell.php
