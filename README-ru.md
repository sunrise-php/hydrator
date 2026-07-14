# Strictly typed object hydration and value casting

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/build.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)

Языки: [🇬🇧 English](README.md) | [🇷🇺 Русский](README-ru.md)

Пакет гидрирует строго типизированные объекты из массивов и JSON и приводит отдельные значения к заданным PHP-типам.

Публичный API находится в пространстве имен `Sunrise\Hydrator`.

## Установка

```bash
composer require sunrise/hydrator
```

## Гидрация объектов

```php
use Sunrise\Hydrator\Annotation\Filter;
use Sunrise\Hydrator\Annotation\ItemType;
use Sunrise\Hydrator\Hydrator;

final class CreateUserRequest
{
    #[Filter('trim')]
    public string $email;

    public bool $isActive;

    #[ItemType('string', limit: 10)]
    public array $roles = [];
}

$request = (new Hydrator())->hydrate(CreateUserRequest::class, [
    'email' => ' user@example.com ',
    'isActive' => 'true',
    'roles' => ['user', 'editor'],
]);
```

Гидратор:

- создает объект без вызова конструктора либо заполняет переданный объект;
- обрабатывает все нестатические свойства независимо от их видимости;
- пропускает свойства с `#[Ignore]`;
- рекурсивно гидрирует вложенные объекты;
- собирает ошибки всех свойств в одном `InvalidDataException`.

Свойство обязательно, если оно не инициализировано и для него не задано значение по умолчанию. Значение по умолчанию можно задать в объявлении свойства, в одноименном параметре конструктора или через `#[DefaultValue]`.

### Гидрация из JSON

```php
$request = (new Hydrator())->hydrateWithJson(
    CreateUserRequest::class,
    '{"email":"user@example.com","isActive":true}'
);
```

Корневым значением JSON должен быть объект или массив.

### Приведение значения

```php
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\Type;

$value = (new Hydrator())->castValue(
    '42',
    Type::fromName(BuiltinType::INT)
);
```

Результат — целое число `42`.

## Поддерживаемые типы

| PHP-тип | Допустимые входные значения |
| --- | --- |
| `mixed` | Любое значение |
| `bool` | `bool` или строка `1`, `0`, `true`, `false`, `yes`, `no`, `on`, `off` |
| `int` | `int` или строковое представление целого числа |
| `float` | `float`, `int` или строковое представление числа |
| `string` | `string` или `int` |
| `array` | `array` или `stdClass` |
| `DateTimeImmutable` и наследники | Строка заданного формата; целое число или строка для формата `U` |
| `DateInterval` | Строка, принимаемая конструктором `DateInterval` |
| `DateTimeZone` | Идентификатор часового пояса |
| `BackedEnum` | Значение перечисления типа `int` или `string` |
| Пользовательский класс | `array` или `stdClass` |
| Класс, реализующий `ArrayAccess` | `array` или `stdClass` |

При наличии соответствующих пакетов также поддерживаются:

- `MyCLabs\Enum\Enum`;
- `Ramsey\Uuid\UuidInterface`;
- наследники `Symfony\Component\Uid\AbstractUid`.

`null` принимается только для типа, допускающего `null`. Пустая строка после удаления окружающих пробелов считается пустым значением при преобразовании логических значений, чисел, дат, интервалов, часовых поясов, перечислений и идентификаторов.

Объединения и пересечения типов не поддерживаются.

## Массивы и коллекции

`#[ItemType]` задает тип элементов массива или коллекции и при необходимости ограничивает их количество:

```php
use Sunrise\Hydrator\Annotation\ItemType;

final class OrderRequest
{
    #[ItemType(OrderItemRequest::class, limit: 100)]
    public array $items;
}
```

Каждый элемент будет приведен к указанному типу. Параметр `allowsNull` разрешает `null` в элементах:

```php
#[ItemType('int', allowsNull: true, limit: 100)]
public array $ids;
```

Для классов, реализующих `ArrayAccess`, тип элементов также можно задать типом последнего параметра конструктора с `...`:

```php
final class UserCollection extends ArrayObject
{
    public function __construct(User ...$users)
    {
        parent::__construct($users);
    }
}
```

### Тип элементов из PHPDoc

Гидратор может читать тип элементов массива из `@var`:

```php
final class OrderRequest
{
    /** @var list<OrderItemRequest> */
    public array $items;
}

$hydrator = new Hydrator(isDocBlockReaderEnabled: true);
```

Для этого должен быть установлен пакет `phpdocumentor/reflection-docblock`. Чтение PHPDoc по умолчанию выключено. `#[ItemType]` имеет приоритет над `@var`.

```bash
composer require phpdocumentor/reflection-docblock
```

## Атрибуты

| Атрибут | Назначение |
| --- | --- |
| `#[Alias('external-name')]` | Задает входной ключ свойства |
| `#[Context([...])]` | Дополняет контекст для свойства или параметра |
| `#[DefaultValue(...)]` | Задает значение отсутствующего свойства |
| `#[Filter(...)]` | Преобразует входное значение до приведения типа |
| `#[Format('...')]` | Задает формат даты и времени |
| `#[Ignore]` | Исключает свойство из гидрации |
| `#[ItemType(...)]` | Задает тип и предельное количество элементов |

`#[Filter]` можно указать несколько раз. Фильтры применяются последовательно:

```php
use Sunrise\Hydrator\Annotation\Filter;

#[Filter('trim')]
#[Filter('strtolower')]
public string $email;
```

## Дата, время и контекст

По умолчанию `DateTimeImmutable` принимает формат `DateTimeInterface::RFC3339_EXTENDED`.

Формат и часовой пояс можно задать для экземпляра гидратора:

```php
use DateTimeInterface;
use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Hydrator;

$hydrator = new Hydrator(context: [
    ContextKey::TIMESTAMP_FORMAT => DateTimeInterface::RFC3339_EXTENDED,
    ContextKey::TIMEZONE => 'Europe/Belgrade',
]);
```

Контекст отдельной операции передается в `hydrate()` или `castValue()`. Для свойства или параметра используются `#[Context]` и `#[Format]`:

```php
use Sunrise\Hydrator\Annotation\Context;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\Dictionary\ContextKey;

#[Format('Y-m-d')]
#[Context([ContextKey::TIMEZONE => 'UTC'])]
public DateTimeImmutable $date;
```

Порядок приоритета:

1. контекст атрибута;
2. контекст операции;
3. контекст гидратора.

## Ошибки

`hydrate()` собирает ошибки отдельных значений в `InvalidDataException`:

```php
use Sunrise\Hydrator\Exception\InvalidDataException;

try {
    $request = $hydrator->hydrate(CreateUserRequest::class, $data);
} catch (InvalidDataException $e) {
    foreach ($e->getExceptions() as $error) {
        echo $error->getPropertyPath() . ': ' . $error->getMessage();
    }
}
```

`InvalidValueException` содержит:

- путь к свойству;
- код ошибки;
- шаблон сообщения и его параметры;
- исходное значение;
- домен перевода.

`hydrateWithJson()` также использует `InvalidDataException` для ошибок декодирования JSON. `castValue()` выбрасывает `InvalidValueException` для отдельного значения и `InvalidDataException` для ошибок вложенных объектов и массивов. `InvalidObjectException` используется для неподдерживаемого типа или объекта, который невозможно создать.

При установленном `symfony/validator` методы `InvalidValueException::getViolation()` и `InvalidDataException::getViolations()` возвращают нарушения в формате Symfony Validator.

## Пользовательские преобразователи типов

Преобразователь реализует `TypeConverterInterface`. Если целевой тип ему не подходит, метод завершается без результата. Если подходит — возвращает результат через `yield` либо выбрасывает исключение гидрации.

```php
use Generator;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

final class MoneyTypeConverter implements TypeConverterInterface
{
    public function castValue(
        $value,
        Type $type,
        array $path,
        array $context
    ): Generator {
        if ($type->getName() !== Money::class) {
            return;
        }

        if (!is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        yield Money::fromString($value);
    }

    public function getWeight(): int
    {
        return 100;
    }
}
```

Преобразователь передается в конструктор или добавляется позднее:

```php
$hydrator = new Hydrator(typeConverters: [
    new MoneyTypeConverter(),
]);

$hydrator->addTypeConverter(new MoneyTypeConverter());
```

Преобразователи вызываются по убыванию веса. Для доступа к гидратору или компоненту чтения атрибутов реализуйте `HydratorAwareInterface` или `AnnotationReaderAwareInterface`.

## Совместимость

Пакет требует PHP 7.4 или новее.

На PHP 8.0 и новее атрибуты читаются автоматически. На PHP 7.4 для `Alias`, `Context`, `DefaultValue`, `Format`, `Ignore` и `ItemType` можно использовать Doctrine Annotations:

```bash
composer require doctrine/annotations
```

```php
use Sunrise\Hydrator\AnnotationReader\DoctrineAnnotationReader;

$hydrator->setAnnotationReader(DoctrineAnnotationReader::default());
```

На PHP 7 поддерживается только сам `DateTimeImmutable`, без наследников. Встроенные перечисления поддерживаются начиная с PHP 8.1.

Устаревшие имена `#[Subtype]` и `#[Relationship]` сохранены для обратной совместимости. В новом коде используйте `#[ItemType]`.
