<?php

use Slexx\Lang\Lang;
use Slexx\Config\Config;
use PHPUnit\Framework\TestCase;
use Slexx\Lang\Exceptions\FileNotExistsException;
use Slexx\Lang\Exceptions\UndefinedLocaleException;

class LangTest extends TestCase
{
    public function testHasFile()
    {
        $this->assertFalse(Lang::hasFile('ru', 'main'));
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        $this->assertTrue(Lang::hasFile('ru', 'main'));
        Lang::removeFile('ru', 'main');
    }

    public function testGetFile()
    {
        $this->assertFalse(Lang::hasFile('ru', 'main'));
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        $this->assertTrue(Lang::getFile('ru', 'main') instanceof Config);
        Lang::removeFile('ru', 'main');
    }

    public function testRemoveFile()
    {
        $this->assertFalse(Lang::hasFile('ru', 'main'));
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        $this->assertTrue(Lang::hasFile('ru', 'main'));
        Lang::removeFile('ru', 'main');
        $this->assertFalse(Lang::hasFile('ru', 'main'));
    }

    public function testSetFile()
    {
        $this->assertFalse(Lang::hasFile('ru', 'main'));
        try {
            Lang::setFile('ru', 'main', __DIR__ . '/qwerty.json');
        } catch (FileNotExistsException $exception) {
            $this->assertTrue(true);
        }
    }

    public function testGetLocales()
    {
        $this->assertEquals([], Lang::getLocales());
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        $this->assertEquals(['ru'], Lang::getLocales());
        Lang::removeFile('ru', 'main');
    }

    public function testParseAcceptLanguage()
    {
        $this->assertEquals([
            [
                'code' => 'fr',
                'region' => 'CH',
                'quality' => 1.0,
            ],
            [
                'code' => 'fr',
                'region' => null,
                'quality' => 0.9,
            ],
            [
                'code' => 'en',
                'region' => null,
                'quality' => 0.8,
            ],
            [
                'code' => 'de',
                'region' => null,
                'quality' => 0.7,
            ],
            [
                'code' => '*',
                'region' => null,
                'quality' => 0.5,
            ],
        ], Lang::parseAcceptLanguage('fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'));
    }

    public function testHasLocale()
    {
        $this->assertFalse(Lang::hasLocale('ru'));
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        $this->assertTrue(Lang::hasLocale('ru'));
        Lang::removeFile('ru', 'main');
        $this->assertFalse(Lang::hasLocale('ru'));
    }

    public function testSetLocale()
    {
        $this->assertEquals('en', Lang::getLocale());
        $this->assertFalse(Lang::hasLocale('ru'));
        try {
            Lang::setLocale('ru');
        } catch (UndefinedLocaleException $e) {
            $this->assertTrue(true);
        }
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        $this->assertTrue(Lang::hasLocale('ru'));
        Lang::setLocale('ru');
        $this->assertEquals('ru', Lang::getLocale());
        Lang::removeFile('ru', 'main');
        $this->assertFalse(Lang::hasLocale('ru'));
    }

    public function testRaw()
    {
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        Lang::setFile('en', 'main', __DIR__ . '/en.json');
        Lang::setLocale('ru');
        $this->assertEquals('Привет, :name!', Lang::raw('main:hello'));
        Lang::setLocale('en');
        $this->assertEquals('Hello, :name!', Lang::raw('main:hello'));
    }

    public function testProps()
    {
        $this->assertEquals('Hello, Alex!', Lang::props('Hello, :name!', ['name' => 'Alex']));
    }

    public function testTranslate()
    {
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        Lang::setFile('en', 'main', __DIR__ . '/en.json');
        Lang::setLocale('ru');
        $this->assertEquals('Привет, Алексей!', Lang::translate('main:hello', ['name' => 'Алексей']));
        Lang::setLocale('en');
        $this->assertEquals('Hello, Alex!', Lang::translate('main:hello', ['name' => 'Alex']));
    }

    public function testPlural()
    {
        Lang::setFile('ru', 'main', __DIR__ . '/ru.json');
        Lang::setFile('en', 'main', __DIR__ . '/en.json');
        Lang::setLocale('ru');
        $this->assertEquals('1 комментарий', Lang::plural('main:comments', 1));
        $this->assertEquals('2 комментария', Lang::plural('main:comments', 2));
        $this->assertEquals('45 комментариев', Lang::plural('main:comments', 45));
        Lang::setLocale('en');
        $this->assertEquals('1 comment', Lang::plural('main:comments', 1));
        $this->assertEquals('2 comments', Lang::plural('main:comments', 2));
        $this->assertEquals('45 comments', Lang::plural('main:comments', 45));
    }
}