<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2020 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace App\Test\TestCase\Form;

use App\Form\Control;
use App\Form\Form;
use App\Form\Options;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \App\Form\Form} Test Case
 */
#[CoversClass(Form::class)]
#[CoversMethod(Form::class, 'getMethod')]
class FormTest extends TestCase
{
    /**
     * Data provider for `testGetMethod` test case.
     *
     * @return array
     */
    public static function getMethodProvider(): array
    {
        return [
            'name with chars to remove 1' => [
                [
                    'class' => Options::class,
                    'methodName' => 'old_password',
                ],
                [Options::class, 'oldPassword'],
            ],
            'name with chars to remove 2' => [
                [
                    'class' => Options::class,
                    'methodName' => 'confirm_password',
                ],
                [Options::class, 'confirmPassword'],
            ],
            'format email' => [
                [
                    'class' => Control::class,
                    'methodName' => 'whatever',
                    'format' => 'email',
                ],
                [Control::class, 'email'],
            ],
            'format uri' => [
                [
                    'class' => Control::class,
                    'methodName' => 'whatever',
                    'format' => 'uri',
                ],
                [Control::class, 'uri'],
            ],
        ];
    }

    /**
     * Test `getMethod` method
     *
     * @param array $options The options
     * @param array $expected The expected method array
     * @return void
     */
    #[DataProvider('getMethodProvider')]
    public function testGetMethod(array $options, array $expected): void
    {
        $class = (string)Hash::get($options, 'class');
        $methodName = (string)Hash::get($options, 'methodName');
        $format = (string)Hash::get($options, 'format');
        if ($format) {
            $actual = Form::getMethod($class, $methodName, $format);
        } else {
            $actual = Form::getMethod($class, $methodName);
        }

        static::assertSame($expected, $actual);
    }

    /**
     * Test `getMethod` method exception 'not callable'
     *
     * @return void
     */
    public function testGetMethodNotCallable(): void
    {
        $methodName = 'dummy';
        $expected = new InvalidArgumentException(sprintf('Method "%s" is not callable', $methodName));
        static::expectException(get_class($expected));
        static::expectExceptionMessage($expected->getMessage());
        Form::getMethod(Form::class, $methodName);
    }
}
