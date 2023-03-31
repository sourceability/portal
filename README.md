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

## Installation

```
composer require sourceability/portal
```

### Trying out

You can try out YAML spells with docker:

```
git clone https://github.com/sourceability/portal.git
cd portal
make php
bin/portal ./examples/csv_headers.yaml
```

### Symfony support

The library includes a Symfony bundle.

Add the bundle to `config/bundles.php`:
```php
return [
    // ...
    Sourceability\Portal\Bundle\SourceabilityPortalBundle::class => ['all' => true],
];
```

Then define the `OPENAI_API_KEY=sk-XXX` environment variable, for example in `.env.local`.

You can also configure the bundle:
```yaml
# config/packages/sourceability_portal.yaml
sourceability_portal:
    openai_api_key: '%my_openai_api_key%'
```

You can invoke your service spells using their FQCN with the cast command (don't forget the quotes):
```
bin/console portal:cast 'App\Portal\MySpell'
```

You can also define a short name with the `#[AutoconfigureSpell]` attribute:
```php
use Sourceability\Portal\Bundle\DependencyInjection\Attribute\AutoconfigureSpell;

#[AutoconfigureSpell('Categorize')]
class CategorizeSpell implements Spell
{
```

And invoke the spell with `bin/console portal:cast Categorize`

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

You can think of a Spell as a way to create a function whose "implementation" is a GPT prompt:
```php
$spell = new StaticSpell(
    schema: [
        'type' => 'array',
        'items' => ['type' => 'string']
    ],
    prompt: 'Synonyms of {{ input }}'
);

/** @var callable(string): array<string> $generateSynonyms */
$generateSynonyms = $portal->callableFromSpell($spell);

dump($generateSynonyms('car'));

array:5 [▼
  0 => "automobile"
  1 => "vehicle"
  2 => "motorcar"
  3 => "machine"
  4 => "transport"
]
```

```php
use Sourceability\Portal\Spell\Spell;

/*
 * @implements Spell<TInput, TOutput>
 */
class MySpell implements Spell
```
A spell is defined by its Input/Output types `TInput` and `TOutput`.
So for example, a spell that accepts a number and returns an array of string, would use `Spell<int, string<string>>`.

### `getSchema`

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
        'type' => 'object',
        'properties' => [
            'release' => [
                'description' => 'The release reference/key.',
                'examples' => ['v1.0.1', 'rc3', '2022.48.2'],
            ]
        ],
    ];
}
```

Note that you can also leverage libraries that define a DSL to build schemas:
- [goldspecdigital/oooas][goldspecdigital/oooas] - see [examples/goldspecdigital-oooas](./examples/goldspecdigital-oooas)
- [swaggest/json-schema][swaggest/php-json-schema] - see [examples/swaggest](./examples/swaggest)

### `getPrompt`

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

### `transcribe`
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

### `getExamples`

The `getExamples` method returns 0 or many inputs examples. This is very useful when iterating on a prompt.
```php
/**
 * @return array<TInput>
 */
public function getExamples(): array;
```

### Casting

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

## `$portal->transfer`

If you don't need the Spell `getExamples` and `transcribe`, you can use `transfer`:
```php
$transferResult = $portal->transfer(
    ['type' => 'string'], // output schema
    'The prompt'
);
$transferResult->value; // the json decoded value
```

## CLI

You can pass your own JSON example to the portal cli:
```
bin/portal spell.yaml '[{"hello":["worlds"]},{"hello":[]}]'
```

Use `-v`, `-vv`, `-vvv` to print more information like the prompts or the OpenAI API requests/responses.

## ApiPlatformSpell

The `ApiPlatformSpell` uses [API Platform][api-platform]'s to generate the JSON Schema but also to deserialize the JSON result.

You must implement the following methods:
- `getClass`
- `getPrompt`

The following are optional:
- `isCollection` is false by default, you can return true instead
- `getExamples` is empty by default, you can add your examples

```php
use Sourceability\Portal\Spell\ApiPlatformSpell;

/**
 * @extends ApiPlatformSpell<string, array<Part>>
 */
class PartListSpell extends ApiPlatformSpell
{
    public function getExamples(): array
    {
        return [
            'smartwatch',
            'bookshelf speaker',
        ];
    }

    public function getPrompt($input): string
    {
        return sprintf('A list of parts to build a %s.', $input);
    }
    
    protected function isCollection(): bool
    {
        return true;
    }
    
    protected function getClass(): string
    {
        return Part::class;
    }
}
```

You can then use the `#[ApiProperty]` attribute to add context to your schema:
```php
use ApiPlatform\Metadata\ApiProperty;

class Part
{
    #[ApiProperty(
        description: 'Product description',
        schema: ['maxLength' => 100],
    )]
    public string $description;
}
```

## Examples

See [./examples/](./examples).

[json_schema]: https://json-schema.org
[json_schema_description]: https://www.learnjsonschema.com/2020-12/meta-data/description/
[json_schema_examples]: https://www.learnjsonschema.com/2020-12/meta-data/examples/
[blog_gpt_json_schema]: https://blog.humphd.org/pouring-language-through-shape/
[Spell.php]: src/Spell/Spell.php
[api-platform]: https://api-platform.com
[goldspecdigital/oooas]: https://github.com/goldspecdigital/oooas
[swaggest/php-json-schema]: https://github.com/swaggest/php-json-schema
