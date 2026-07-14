# Strictly typed object hydration and value casting

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/build.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)

Языки: [🇬🇧 English](README.md) | [🇷🇺 Русский](README-ru.md)

Пакет гидрирует строго типизированные объекты из массивов и JSON и приводит отдельные значения к заданным PHP-типам.

## Установка

```bash
composer require sunrise/hydrator
```

Пакет поддерживает PHP 7.4 и новее. Примеры в этом README ориентированы на PHP 8+.

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
- пропускает свойства с `#[Ignore]`;
- игнорирует входные ключи, которым не соответствует ни одно свойство;
- рекурсивно гидрирует вложенные объекты;
- собирает ошибки отдельных свойств и элементов в одном `InvalidDataException`.

Входное значение обязательно, если соответствующее свойство не инициализировано и для него не задано значение по умолчанию. Значение по умолчанию можно задать в объявлении свойства, в одноименном параметре конструктора или через `#[DefaultValue]`.

Значение по умолчанию используется только при отсутствии входного ключа. Переданный `null` считается входным значением и проверяется согласно типу свойства.

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
| Конкретное backed-перечисление | Значение соответствующего backing-типа; для `int` также принимается строковое представление целого числа |
| Инстанцируемый пользовательский класс | `array` или `stdClass` |
| Класс, реализующий `ArrayAccess` | `array` или `stdClass` |

Дополнительно поддерживаются:

| Тип | Пакет |
| --- | --- |
| Наследник `MyCLabs\Enum\Enum` | `myclabs/php-enum` |
| `Ramsey\Uuid\UuidInterface` | `ramsey/uuid` |
| Наследники `Symfony\Component\Uid\AbstractUid` | `symfony/uid` |

`null` принимается только для типа, допускающего `null`.
Для логических значений, чисел, дат, интервалов, часовых поясов, перечислений, UUID и UID пустая строка после удаления окружающих пробелов трактуется как отсутствие значения: для nullable-типа возвращается `null`, иначе выбрасывается `InvalidValueException`.
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

Для инстанцируемого класса, реализующего `ArrayAccess`, тип элементов также можно задать типом последнего параметра конструктора с `...`:

```php
final class UserCollection extends ArrayObject
{
    public function __construct(User ...$users)
    {
        parent::__construct($users);
    }
}
```

При гидрации конструктор не вызывается: гидратор только читает тип последнего вариативного параметра и создает объект без конструктора. Явный `#[ItemType]` имеет приоритет над типом параметра конструктора.
Если коллекция выбрасывает `OverflowException` при добавлении элемента, гидратор трактует это как превышение допустимого количества элементов и возвращает пользователю ошибку переполнения массива.

### Тип элементов из PHPDoc

Гидратор может читать тип элементов свойства `array` из `@var`:

```php
final class OrderRequest
{
    /** @var list<OrderItemRequest> */
    public array $items;
}

$hydrator = new Hydrator(isDocBlockReaderEnabled: true);
```

Чтение PHPDoc по умолчанию выключено. `#[ItemType]` имеет приоритет над `@var`.
Чтение `@var` не применяется к классам, реализующим `ArrayAccess`. Для них используйте `#[ItemType]` или тип вариативного параметра конструктора.

## Атрибуты

| Атрибут | Назначение |
| --- | --- |
| `#[Alias('external-name')]` | Задает входной ключ свойства |
| `#[Context([...])]` | Задает или переопределяет значения контекста для свойства или параметра |
| `#[DefaultValue(...)]` | Задает значение при отсутствии входного ключа |
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

`#[Filter]` доступен только на PHP 8.0 и новее.

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
        echo $error->getPropertyPath() . ': ' . $error->getMessage() . PHP_EOL;
    }
}
```

`InvalidValueException` содержит:

- `getPropertyPath()` — путь к значению через точку;
- `getErrorCode()` — код ошибки;
- `getMessage()` — сформированное сообщение;
- `getMessageTemplate()` — шаблон сообщения;
- `getMessagePlaceholders()` — параметры шаблона;
- `getInvalidValue()` — исходное значение;
- `getTranslationDomain()` — домен перевода.

Исключения методов:

- `hydrate()` выбрасывает `InvalidDataException` при ошибках входных значений и `InvalidObjectException`, если объект нельзя создать или свойство имеет неподдерживаемый тип;
- `hydrateWithJson()` дополнительно использует `InvalidDataException` для ошибок декодирования JSON и недопустимого корневого значения;
- `castValue()` выбрасывает `InvalidValueException` для одного недопустимого значения, `InvalidDataException` для ошибок вложенного объекта или массива и `InvalidObjectException` для неподдерживаемого целевого типа.

При установленном `symfony/validator` методы `InvalidValueException::getViolation()` и `InvalidDataException::getViolations()` возвращают нарушения в формате Symfony Validator.

## Пользовательские преобразователи типов

Преобразователь реализует `TypeConverterInterface`. Если тип не поддерживается, метод завершается без результата. Если поддерживается — возвращает результат через `yield` или выбрасывает исключение.

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
