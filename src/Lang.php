<?php

namespace Slexx\Lang;

use Slexx\Config\Config;
use Slexx\Pattern\Pattern;
use Slexx\Lang\Exceptions\FileNotExistsException;
use Slexx\Lang\Exceptions\NoLocalizationException;
use Slexx\Lang\Exceptions\UndefinedLocaleException;
use Slexx\Lang\Exceptions\NamespaceNotExistsException;
use Slexx\Lang\Exceptions\UndefinedPluralFunctionException;

class Lang
{
    /**
     * @var array
     */
    protected static $files = [];

    /**
     * @var string
     */
    protected static $currentLocale = 'en';

    /**
     * @var callable[]
     */
    protected static $pluralFunctions = [];

    /**
     * Устанавливает файл локализации
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @param string $namespace - Пространство имён для строк локализации
     * @param string $path - Путь к файлу (yaml, json, php, ini)
     * @return void
     * @throws FileNotExistsException - Будет брошено если файла не существует
     */
    public static function setFile(string $locale, string $namespace, string $path)
    {
        if (!file_exists($path)) {
            throw new FileNotExistsException($path);
        }
        if (!isset(self::$files[$locale])) {
            self::$files[$locale] = [];
        }
        self::$files[$locale][$namespace] = new Config($path);
    }

    /**
     * Проверка существования файла локализации
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @param string $namespace - Пространство имён для строк локализации
     * @return bool
     */
    public static function hasFile(string $locale, string $namespace): bool
    {
        return isset(self::$files[$locale]) && isset(self::$files[$locale][$namespace]);
    }

    /**
     * Получение пути к файлу локализации
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @param string $namespace - Пространство имён для строк локализации
     * @return string|null
     */
    public static function getFile(string $locale, string $namespace)
    {
        if (!self::hasFile($locale, $namespace)) {
            return null;
        }
        return self::$files[$locale][$namespace];
    }

    /**
     * Удаление файла локализации
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @param string $namespace - Пространство имён для строк локализации
     * @return void
     */
    public static function removeFile(string $locale, string $namespace)
    {
        if (self::hasFile($locale, $namespace)) {
            unset(self::$files[$locale][$namespace]);
        }
    }

    /**
     * Получение списка всех доступных локализаций
     * @return string[]
     */
    public static function getLocales(): array
    {
        $result = [];
        foreach(self::$files as $locale => $files) {
            if (count($files)) {
                $result[] = $locale;
            }
        }
        return $result;
    }

    /**
     * Парсинг HTTP заголовка Accept-Language
     * @return array
     * @param string [$acceptLanguage]
     * @example:
     * var_dump(Lang::parseAcceptLanguage());
     * // [
     * //     ['code' => 'ru', 'region' => 'RU', 'quality' => 1],
     * //     ['code' => 'ru', 'region' => null, 'quality' => 0.8],
     * //     ['code' => 'en', 'region' => 'US', 'quality' => 0.6],
     * //     ['code' => 'en', 'region' => null, 'quality' => 0.4],
     * //     ...
     * // ]
     */
    public static function parseAcceptLanguage(string $acceptLanguage = null): array
    {
        if (!is_string($acceptLanguage)) {
            $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        $result = [];

        $pattern = new Pattern('<code:[a-z\*]+>[-<region:[A-Z]+>][;q=<quality:float>]');
        $pattern->default('quality', 1.0);

        foreach(explode(',', $acceptLanguage) as $c) {
            $c = trim($c);
            $result[] = $pattern->match($c);
        }
        return $result;
    }

    /**
     * Проверка доступности локализации
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @return bool
     */
    public static function hasLocale(string $locale): bool
    {
        return isset(self::$files[$locale]) && count(self::$files[$locale]);
    }

    /**
     * Установка локализации
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @return void
     * @throws UndefinedLocaleException - Если локализация отсуцтвует
     */
    public static function setLocale(string $locale)
    {
        if (!self::hasLocale($locale)) {
            throw new UndefinedLocaleException($locale);
        }
        self::$currentLocale = $locale;
    }

    /**
     * Получение локализации
     * @return string
     */
    public static function getLocale(): string
    {
        return self::$currentLocale;
    }

    /**
     * Ищет наиболее подходящюю локализацию по заголовку Accept-Language. Перед использованием метода следует указать файлы локализации с помощью метода setFile
     * @param string [$default] - Локализация по умолчанию, в случае если парсинг Accept-Language не дал результатов
     * @return string - ISO код наиболее подходящей локализации
     */
    public static function searchLocale(string $default = 'en'): string
    {
        $languages = self::parseAcceptLanguage();
        foreach($languages as $language) {
            if ($language['code'] === '*') {
                return $default;
            }
            if ($language['region'] && self::hasLocale($language['code'] . '_' . $language['region'])) {
                return $language['code'] . '_' . $language['region'];
            }
            if (self::hasLocale($language['code'])) {
                return $language['code'];
            }
        }
        return $default;
    }

    /**
     * Получение сырого не обработанного перевода
     * @param string $path - Клуч перевода вида: пространство_имён:ключ_в_массиве
     * @return string|array|null
     * @throws NamespaceNotExistsException - Если пространство имён не существует
     * @throws NoLocalizationException - Если локализация не существует
     */
    public static function raw(string $path)
    {
        list($namespace, $key) = explode(':', $path);
        if (!self::hasFile(self::getLocale(), $namespace)) {
            throw new NamespaceNotExistsException($namespace);
        }
        return self::getFile(self::getLocale(), $namespace)->get($key);
    }

    /**
     * Замена переменных в строке
     * @param string $string - Строка в которой будет произведена замена
     * @param array $props - Массив параметров
     * @return string
     * @example:
     * echo Lang::props('Hello, :name!', ['name' => 'World']);
     * // Hello, World!
     */
    public static function props(string $string, array $props): string
    {
        foreach($props as $key => $value) {
            $string = str_replace(':' . $key, $value, $string);
        }
        return $string;
    }

    /**
     * Получение перевода с заменой параметров
     * @param string $path - Клуч перевода вида: пространство_имён:ключ_в_массиве
     * @param array [$props] - Массив параметров
     * @return string
     * @throws NamespaceNotExistsException - Если пространство имён не существует
     * @throws NoLocalizationException - Если локализация не существует
     * @example:
     * messages.php:
     * ```php
     * <?php
     * return [
     * 'hello' => 'Привет, :name!',
     * ];
     * ```
     *
     * index.php:
     * ```php
     * Lang::setFile('ru', 'messages', __DIR__ . '/messages.php');
     * Lang::setLocale('ru');
     *
     * echo Lang::translate('messages:hello', ['name' => 'Алексей']);
     * // Выведет: 'Привет, Алексей!'
     */
    public static function translate(string $path, array $props = []): string
    {
        $raw = self::raw($path);
        if (!is_string($raw)) {
            throw new NoLocalizationException();
        }
        return self::props($raw, $props);
    }

    /**
     * Установка функции плюрализации для языка
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @param string $function - Функции плюларизации
     * @return void
     * @example:
     * Lang::setPluralFunction('ru', function($n) {
     *     return $n%10==1&&$n%100!=11?0:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?1:2);
     * });
     * Lang::setPluralFunction('en', function($n) {
     *     return $n>1?1:0;
     * });
     */
    public static function setPluralFunction(string $locale, $function)
    {
        self::$pluralFunctions[$locale] = $function;
    }

    /**
     * Удаление функции плюрализации для языка
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @return void
     */
    public static function removePluralFunction(string $locale)
    {
        unset(self::$pluralFunctions[$locale]);
    }

    /**
     * Проверка существования функции плюрализации для языка
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @return bool
     */
    public static function hasPluralFunction(string $locale): bool
    {
        return isset(self::$pluralFunctions[$locale]) && is_callable(self::$pluralFunctions[$locale]);
    }

    /**
     * Получение функции плюрализации для языка
     * @param string $locale - ISO код язака (ru, en_AU, ru_RU...)
     * @return callable|null
     */
    public static function getPluralFunction(string $locale)
    {
        if (!self::hasPluralFunction($locale)) {
            return null;
        }
        return self::$pluralFunctions[$locale];
    }

    /**
     * Плюрализация перевода
     * @param string $path - Клуч перевода вида: пространство_имён:ключ_в_массиве
     * @param int $counter - Сщётчик
     * @param array [$props] - Массив параметров
     * @return string
     * @throws UndefinedPluralFunctionException - Если для языка нет фунции плюларизации
     * @throws NoLocalizationException - Если локализация не существует
     * @example:
     * blog.php:
     * ```php
     * <?php
     * return [
     *     'comments' => [':count комментарий', ':count комментария', ':count коментариев'],
     * ];
     * ```
     *
     * index.php:
     * ```php
     * Lang::setFile('ru', 'blog', __DIR__ . '/blog.php');
     * Lang::setLocale('ru');
     *
     * Lang::plural('blog:comments', 1); // 1 комментарий
     * Lang::plural('blog:comments', 2); // 2 комментария
     * Lang::plural('blog:comments', 45); // 45 коментариев
     */
    public static function plural(string $path, int $counter, array $props = []): string
    {
        $raw = self::raw($path);
        if (!is_array($raw)) {
            throw new NoLocalizationException();
        }
        $locale = self::getLocale();
        $pluralFunction = self::getPluralFunction($locale);
        if ($pluralFunction === null) {
            throw new UndefinedPluralFunctionException($locale);
        }
        $form = $pluralFunction($counter);
        if (!isset($raw[$form]) || !is_string($raw[$form])) {
            throw new NoLocalizationException();
        }
        return self::props($raw[$form], array_merge($props, ['count' => $counter]));
    }
}

Lang::setPluralFunction('ru', function($n) {
    return $n%10==1&&$n%100!=11?0:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?1:2);
});

Lang::setPluralFunction('en', function($n) {
    return $n>1?1:0;
});
