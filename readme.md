# Internalization library

Класс для управления локализацией, не привязан к файловой системе, может работать с любой структурой файлов и папок. Умеет работать с json, yaml, ini и php файлами.

[![Latest Stable Version](https://poser.pugx.org/slexx/lang/v/stable)](https://packagist.org/packages/slexx/lang) [![Total Downloads](https://poser.pugx.org/slexx/lang/downloads)](https://packagist.org/packages/slexx/lang) [![Latest Unstable Version](https://poser.pugx.org/slexx/lang/v/unstable)](https://packagist.org/packages/slexx/lang) [![License](https://poser.pugx.org/slexx/lang/license)](https://packagist.org/packages/slexx/lang)

```php
// Устанавливаем файлы с переводами
Lang::setFile('ru', 'messages', __DIR__ . '/locales/ru/messages.json');
Lang::setFile('en', 'messages', __DIR__ . '/locales/en/messages.yaml');

// Устанавливаем локализацию
Lang::setLocale(Lang::searchLocale('ru'));

// Добываем переводы в нужных местах
Lang::translate('messages:key');
```

## Установка
Установка через composer:

```bash
$ composer require slexx/lang
```

## Документация

### Lang::setFile($locale, $namespace, $path)

Устанавливает файл локализации

**Аргументы:**

| Имя          | Тип      | Описание                                |
| ------------ | -------- | --------------------------------------- |
| `$locale`    | `string` | ISO код язака (ru, en_AU, ru_RU...)     |
| `$namespace` | `string` | Пространство имён для строк локализации |
| `$path`      | `string` | Путь к файлу (yaml, json, php, ini)     |

**Исключения:**

| Тип                      | Описание                               |
| ------------------------ | -------------------------------------- |
| `FileNotExistsException` | Будет брошено если файла не существует |

**Возвращает:** `void`

### Lang::hasFile($locale, $namespace)

Проверка существования файла локализации

**Аргументы:**

| Имя          | Тип      | Описание                                |
| ------------ | -------- | --------------------------------------- |
| `$locale`    | `string` | ISO код язака (ru, en_AU, ru_RU...)     |
| `$namespace` | `string` | Пространство имён для строк локализации |

**Возвращает:** `bool`

### Lang::getFile($locale, $namespace)

Получение пути к файлу локализации

**Аргументы:**

| Имя          | Тип      | Описание                                |
| ------------ | -------- | --------------------------------------- |
| `$locale`    | `string` | ISO код язака (ru, en_AU, ru_RU...)     |
| `$namespace` | `string` | Пространство имён для строк локализации |

**Возвращает:** `string`, `null` - Путь к файлу или `null` в случае его отсуцтвия

### Lang::removeFile($locale, $namespace)

Удаление файла локализации

**Аргументы:**

| Имя          | Тип      | Описание                                |
| ------------ | -------- | --------------------------------------- |
| `$locale`    | `string` | ISO код язака (ru, en_AU, ru_RU...)     |
| `$namespace` | `string` | Пространство имён для строк локализации |

**Возвращает:** `void`

### Lang::getLocales()

Получение списка всех доступных локализаций

**Возвращает:** `string[]`

### Lang::props($string, $props)

Замена переменных в строке

| Имя       | Тип      | Описание                                  |
| --------- | -------- | ----------------------------------------- |
| `$string` | `string` | Строка в которой будет произведена замена |
| `$props`  | `array`  | Массив параметров                         |

**Возвращает:** `string`

**Пример:**

```php
echo Lang::props('Hello, :name!', ['name' => 'World']);
// Hello, World!
```

### Lang::parseAcceptLanguage()

Парсинг HTTP заголовка Accept-Language

**Возвращает:** `array`

**Пример:**
```php
var_dump(Lang::parseAcceptLanguage());
// [
//     ['code' => 'ru', 'region' => 'RU', 'quality' => 1],
//     ['code' => 'ru', 'region' => null, 'quality' => 0.8],
//     ['code' => 'en', 'region' => 'US', 'quality' => 0.6],
//     ['code' => 'en', 'region' => null, 'quality' => 0.4],
//     ...
// ]
```

### Lang::hasLocale($locale)

Проверка доступности локализации

**Аргументы:**

| Имя       | Тип      | Описание                            |
| --------- | -------- | ----------------------------------- |
| `$locale` | `string` | ISO код язака (ru, en_AU, ru_RU...) |

**Возвращает:** `bool`

### Lang::setLocale($locale)

Установка локализации

**Аргументы:**

| Имя       | Тип      | Описание                            |
| --------- | -------- | ----------------------------------- |
| `$locale` | `string` | ISO код язака (ru, en_AU, ru_RU...) |

**Исключения:**

| Тип                        | Описание                    |
| -------------------------- | --------------------------- |
| `UndefinedLocaleException` | Если локализация отсуцтвует |

**Возвращает:** `void`

### Lang::getLocale()

Получение локализации

**Возвращает:** `string`

### Lang::searchLocale([$default])

Ищет наиболее подходящюю локализацию по заголовку Accept-Language. Перед использованием метода следует указать файлы локализации с помощью метода setFile

**Аргументы:**

| Имя          | Тип      | Описание                                                                           |
| ------------ | -------- | ---------------------------------------------------------------------------------- |
| `[$default]` | `string` | Локализация по умолчанию, в случае если парсинг Accept-Language не дал результатов |

**Возвращает:** `string` - ISO код наиболее подходящей локализации

### Lang::raw($path)

Получение сырого не обработанного перевода

**Аргументы:**

| Имя     | Тип      | Описание                                             |
| ------- | -------- | ---------------------------------------------------- |
| `$path` | `string` | Клуч перевода вида: пространство_имён:ключ_в_массиве |

**Исключения:**

| Тип                           | Описание                             |
| ----------------------------- | ------------------------------------ |
| `NamespaceNotExistsException` | Если пространство имён не существует |
| `NoLocalizationException`     | Если локализация не существует       |

**Возвращает:** `array`, `string`, `null`

### Lang::translate($path[, $props])

Получение перевода с заменой параметров

**Аргументы:**

| Имя      | Тип      | Описание                                             |
| -------- | -------- | ---------------------------------------------------- |
| $path    | `string` | Клуч перевода вида: пространство_имён:ключ_в_массиве |
| [$props] | `array`  | Массив параметров                                    |

**Исключения:**

| Тип                           | Описание                             |
| ----------------------------- | ------------------------------------ |
| `NamespaceNotExistsException` | Если пространство имён не существует |
| `NoLocalizationException`     | Если локализация не существует       |

**Возвращает:** `string`

**Пример:**

messages.php:
```php
<?php
return [
    'hello' => 'Привет, :name!',
];
```

index.php:
```php
Lang::setFile('ru', 'messages', __DIR__ . '/messages.php');
Lang::setLocale('ru');

echo Lang::translate('messages:hello', ['name' => 'Алексей']);
// Выведет: 'Привет, Алексей!'
```

### Lang::setPluralFunction($locale, $function)

Установка функции плюрализации для языка

| Имя         | Тип      | Описание                            |
| ----------- | -------- | ----------------------------------- |
| `$locale`   | `string` | ISO код язака (ru, en_AU, ru_RU...) |
| `$function` | `array`  | Функции плюларизации                |

**Возвращает:** `void`

**Пример:**

```php
Lang::setPluralFunction('ru', function($n) {
    return $n%10==1&&$n%100!=11?0:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?1:2);
});
Lang::setPluralFunction('en', function($n) {
    return $n>1?1:0;
});
```

### Lang::removePluralFunction($locale)

Удаление функции плюрализации для языка

| Имя         | Тип      | Описание                            |
| ----------- | -------- | ----------------------------------- |
| `$locale`   | `string` | ISO код язака (ru, en_AU, ru_RU...) |

**Возвращает:** `void`

### Lang::hasPluralFunction($locale)

Проверка существования функции плюрализации для языка

| Имя         | Тип      | Описание                            |
| ----------- | -------- | ----------------------------------- |
| `$locale`   | `string` | ISO код язака (ru, en_AU, ru_RU...) |

**Возвращает:** `bool`

### Lang::getPluralFunction($locale)

Получение функции плюрализации для языка

| Имя         | Тип      | Описание                            |
| ----------- | -------- | ----------------------------------- |
| `$locale`   | `string` | ISO код язака (ru, en_AU, ru_RU...) |

**Возвращает:** `callable`, `null`

### Lang::getPluralFunction($path, $counter[, $props])

Плюрализация перевода

| Имя        | Тип      | Описание                                             |
| ---------- | -------- | ---------------------------------------------------- |
| `$path`    | `string` | Клуч перевода вида: пространство_имён:ключ_в_массиве |
| `$counter` | `int`    | Сщётчик                                              |
| `[$props]` | `array`  | Массив параметров                                    |

**Возвращает:** `string`

**Исключения:**

| Тип                                | Описание                               |
| ---------------------------------- | -------------------------------------- |
| `UndefinedPluralFunctionException` | Если для языка нет фунции плюларизации |
| `NoLocalizationException`          | Если локализация не существует         |

**Пример:**

blog.php:
```php
<?php
return [
    'comments' => [':count комментарий', ':count комментария', ':count коментариев'],
];
```

index.php:
```php
Lang::setFile('ru', 'blog', __DIR__ . '/blog.php');
Lang::setLocale('ru');

Lang::plural('blog:comments', 1); // 1 комментарий
Lang::plural('blog:comments', 2); // 2 комментария
Lang::plural('blog:comments', 45); // 45 коментариев
```
