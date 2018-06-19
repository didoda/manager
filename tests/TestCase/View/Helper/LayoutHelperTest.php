<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\LayoutHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * {@see \App\View\Helper\LayoutHelper} Test Case
 *
 * @coversDefaultClass \App\View\Helper\LayoutHelper
 */
class LayoutHelperTest extends TestCase
{
    /**
     * Data provider for `testPrimarySidebar` test case.
     *
     * @return array
     */
    public function primarySidebarProvider() : array
    {
        return [
            'dashboard' => [
                'Dashboard',
                true,
            ],
            'login' => [
                'Login',
                false,
            ],
        ];
    }

    /**
     * Test primarySidebar
     *
     * @param string $name The view name
     * @param bool $expected The expected result
     *
     * @dataProvider primarySidebarProvider()
     * @covers ::primarySidebar()
     */
    public function testPrimarySidebar($name, $expected) : void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $layout = new LayoutHelper(new View($request, $response, $events, $data));
        $result = $layout->primarySidebar();
        static::assertSame($result, $expected);
    }

    /**
     * Data provider for `testSecondarySidebar` test case.
     *
     * @return array
     */
    public function secondarySidebarProvider() : array
    {
        return [
            'import' => [
                'Import',
                null,
                true,
            ],
            'user profile' => [
                'UserProfile',
                null,
                true,
            ],
            'currentModule empty' => [
                null,
                null,
                false,
            ],
            'currentModule not empty' => [
                null,
                'Objects',
                true,
            ],
        ];
    }

    /**
     * Test secondarySidebar
     *
     * @param string $name The view name
     * @param string $currentModule The current module
     * @param bool $expected The expected result
     *
     * @dataProvider secondarySidebarProvider()
     * @covers ::secondarySidebar()
     */
    public function testSecondarySidebar($name, $currentModule, $expected) : void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $view = new View($request, $response, $events, $data);
        $view->set('currentModule', $currentModule);
        $layout = new LayoutHelper($view);
        $result = $layout->secondarySidebar();
        static::assertSame($result, $expected);
    }

    /**
     * Data provider for `testLayoutHeader` test case.
     *
     * @return array
     */
    public function layoutHeaderProvider() : array
    {
        return [
            'dashboard' => [
                'Dashboard',
                false,
            ],
            'login' => [
                'Login',
                false,
            ],
            'objects' => [
                'Objects',
                true,
            ],
        ];
    }

    /**
     * Test layoutHeader
     *
     * @param string $name The view name
     * @param bool $expected The expected result
     *
     * @dataProvider layoutHeaderProvider()
     * @covers ::layoutHeader()
     */
    public function testLayoutHeader($name, $expected) : void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $layout = new LayoutHelper(new View($request, $response, $events, $data));
        $result = $layout->layoutHeader();
        static::assertSame($result, $expected);
    }

    /**
     * Test layoutContent
     *
     * @covers ::layoutContent()
     */
    public function testLayoutContent() : void
    {
        $layout = new LayoutHelper(new View());
        static::assertSame($layout->layoutContent(), true);
    }

    /**
     * Data provider for `testLayoutFooter` test case.
     *
     * @return array
     */
    public function layoutFooterProvider() : array
    {
        return [
            'currentModule empty' => [
                null,
                false,
            ],
            'currentModule not empty' => [
                'Objects',
                true,
            ],
        ];
    }

    /**
     * Test layoutFooter
     *
     * @param string $currentModule The current module
     * @param bool $expected The expected result
     *
     * @dataProvider layoutFooterProvider()
     * @covers ::layoutFooter()
     */
    public function testLayoutFooter($currentModule, $expected) : void
    {
        $view = new View();
        $view->set('currentModule', $currentModule);
        $layout = new LayoutHelper($view);
        $result = $layout->layoutFooter();
        static::assertSame($result, $expected);
    }

    /**
     * Data provider for `testMessages` test case.
     *
     * @return array
     */
    public function messagesProvider() : array
    {
        return [
            'login' => [
                'Login',
                false,
            ],
            'not login' => [
                'Objects',
                true,
            ],
        ];
    }

    /**
     * Test layoutFooter
     *
     * @param string $name The view name
     * @param bool $expected The expected result
     *
     * @dataProvider messagesProvider()
     * @covers ::messages()
     */
    public function testMessages($name, $expected) : void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $layout = new LayoutHelper(new View($request, $response, $events, $data));
        $result = $layout->messages();
        static::assertSame($result, $expected);
    }

    /**
     * Data provider for `testCommandLinkClass` test case.
     *
     * @return array
     */
    public function commandLinkClassProvider() : array
    {
        return [
            'user profile' => [
                'UserProfile',
                'has-background-black icon-user',
            ],
            'import' => [
                'Import',
                'has-background-black icon-download-alt',
            ],
            'objects' => [
                'Objects',
                'commands-menu__module',
            ],
        ];
    }

    /**
     * Test commandLinkClass
     *
     * @param string $name The view name
     * @param string $expected The expected class
     *
     * @dataProvider commandLinkClassProvider()
     * @covers ::commandLinkClass()
     */
    public function testCommandLinkClass($name, $expected) : void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $layout = new LayoutHelper(new View($request, $response, $events, $data));
        $result = $layout->commandLinkClass();
        static::assertSame($result, $expected);
    }
}