<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2019 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace App\Test\TestCase\View\Helper;

use App\Plugin;
use App\Utility\CacheTools;
use App\View\Helper\EditorsHelper;
use App\View\Helper\LayoutHelper;
use App\View\Helper\PermsHelper;
use App\View\Helper\SystemHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Cookie\CookieCollection;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Cake\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \App\View\Helper\LayoutHelper} Test Case
 */
#[CoversClass(LayoutHelper::class)]
#[CoversMethod(LayoutHelper::class, 'commandLinkClass')]
#[CoversMethod(LayoutHelper::class, 'dashboardModuleLink')]
#[CoversMethod(LayoutHelper::class, 'getCsrfToken')]
#[CoversMethod(LayoutHelper::class, 'indexLists')]
#[CoversMethod(LayoutHelper::class, 'isDashboard')]
#[CoversMethod(LayoutHelper::class, 'isLogin')]
#[CoversMethod(LayoutHelper::class, 'messages')]
#[CoversMethod(LayoutHelper::class, 'metaConfig')]
#[CoversMethod(LayoutHelper::class, 'moduleClass')]
#[CoversMethod(LayoutHelper::class, 'moduleCount')]
#[CoversMethod(LayoutHelper::class, 'moduleIcon')]
#[CoversMethod(LayoutHelper::class, 'moduleLink')]
#[CoversMethod(LayoutHelper::class, 'moduleIndexDefaultViewType')]
#[CoversMethod(LayoutHelper::class, 'moduleIndexViewType')]
#[CoversMethod(LayoutHelper::class, 'moduleIndexViewTypes')]
#[CoversMethod(LayoutHelper::class, 'propertyGroup')]
#[CoversMethod(LayoutHelper::class, 'publishStatus')]
#[CoversMethod(LayoutHelper::class, 'showCounter')]
#[CoversMethod(LayoutHelper::class, 'title')]
#[CoversMethod(LayoutHelper::class, 'tr')]
#[CoversMethod(LayoutHelper::class, 'trashLink')]
class LayoutHelperTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->loadRoutes();
    }

    /**
     * Data provider for `testIsDashboard` test case.
     *
     * @return array
     */
    public static function isDashboardProvider(): array
    {
        return [
            'dashboard' => [
                'Dashboard',
                true,
            ],
        ];
    }

    /**
     * Test isDashboard
     *
     * @param string $name The view name
     * @param bool $expected The expected result
     */
    #[DataProvider('isDashboardProvider')]
    public function testIsDashboard(string $name, bool $expected): void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $layout = new LayoutHelper(new View($request, $response, $events, $data));
        $result = $layout->isDashboard();
        static::assertSame($expected, $result);
    }

    /**
     * Data provider for `testIsLogin` test case.
     *
     * @return array
     */
    public static function isLoginProvider(): array
    {
        return [
            'login' => [
                'Login',
                true,
            ],
            'other' => [
                'Trash',
                false,
            ],
        ];
    }

    /**
     * Test isLogin
     *
     * @param string $name The view name
     * @param bool $expected The expected result
     */
    #[DataProvider('isLoginProvider')]
    public function testIsLogin(string $name, bool $expected): void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $layout = new LayoutHelper(new View($request, $response, $events, $data));
        $result = $layout->isLogin();
        static::assertSame($expected, $result);
    }

    /**
     * Data provider for `testMessages` test case.
     *
     * @return array
     */
    public static function messagesProvider(): array
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
     */
    #[DataProvider('messagesProvider')]
    public function testMessages(string $name, bool $expected): void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $layout = new LayoutHelper(new View($request, $response, $events, $data));
        $result = $layout->messages();
        static::assertSame($expected, $result);
    }

    /**
     * Data provider for testModuleClass test case.
     *
     * @return array
     */
    public static function moduleClassProvider(): array
    {
        return [
            'app-module-box locked' => [
                true,
                false,
                'app-module-box locked',
            ],
            'app-module-box-concurrent-editors' => [
                false,
                true,
                'app-module-box-concurrent-editors',
            ],
            'publish status' => [
                false,
                false,
                'app-module-box',
            ],
        ];
    }

    /**
     * Test `moduleClass` method
     *
     * @param bool $locked The locked flag
     * @param bool $concurrent The concurrent editors flag
     * @param string $expected The expected class
     * @return void
     */
    #[DataProvider('moduleClassProvider')]
    public function testModuleClass(bool $locked, bool $concurrent, string $expected): void
    {
        $request = $response = $events = null;
        $view = new View($request, $response, $events, ['name' => 'Objects']);
        $view->set('object', ['id' => 999]);

        $layout = new class ($view) extends LayoutHelper {
            public PermsHelper $Perms;
            public EditorsHelper $Editors;
        };
        if ($locked) {
            $layout->Perms = new class ($view) extends PermsHelper {
                public function isLockedByParents(string $id): bool
                {
                    return true;
                }
            };
        } elseif ($concurrent) {
            $layout->Perms = new class ($view) extends PermsHelper {
                public function isLockedByParents(string $id): bool
                {
                    return false;
                }
            };
            $layout->Editors = new class ($view) extends EditorsHelper {
                public function list(): array
                {
                    return [
                        [
                            'id' => 1,
                            'username' => 'first',
                        ],
                        [
                            'id' => 2,
                            'username' => 'second',
                        ],
                    ];
                }
            };
        } else {
            $layout->Perms = new class ($view) extends PermsHelper {
                public function isLockedByParents(string $id): bool
                {
                    return false;
                }
            };
            $layout->Editors = new class ($view) extends EditorsHelper {
                public function list(): array
                {
                    return [];
                }
            };
        }
        $actual = $layout->moduleClass();
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testModuleLink` test case.
     *
     * @return array
     */
    public static function moduleLinkProvider(): array
    {
        return [
            'user profile' => [
                '<a href="/user_profile" class="has-background-black icon-user">UserProfile</a>',
                'UserProfile',
                [
                    'moduleLink' => ['_name' => 'user_profile:view'],
                ],
            ],
            'import' => [
                '<a href="/import" class="has-background-black icon-download-alt">Import</a>',
                'Import',
                [
                    'moduleLink' => ['_name' => 'import:index'],
                ],
            ],
            'objects' => [
                '<a href="/objects" class="module-item has-background-module-objects"><span class="mr-05">Objects</span></a>',
                'Module',
                [
                    'currentModule' => ['name' => 'objects'],
                ],
            ],
        ];
    }

    /**
     * Test `moduleLink` method
     *
     * @param string $expected The expected link
     * @param string $name The view name
     * @param array $viewVars The view vars
     */
    #[DataProvider('moduleLinkProvider')]
    public function testModuleLink(string $expected, string $name, array $viewVars = []): void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $view = new View($request, $response, $events, $data);
        foreach ($viewVars as $key => $value) {
            $view->set($key, $value);
        }
        $layout = new LayoutHelper($view);
        $result = $layout->moduleLink();
        static::assertSame($expected, $result);
    }

    /**
     * Data provider for `testModuleIndexDefaultViewType` test case.
     *
     * @return array
     */
    public static function moduleIndexDefaultViewTypeProvider(): array
    {
        return [
            'documents' => [
                ['currentModule' => ['name' => 'documents']],
                'list',
            ],
            'folders' => [
                ['currentModule' => ['name' => 'folders']],
                'tree',
            ],
        ];
    }

    /**
     * Test `moduleIndexDefaultViewType` method
     *
     * @param array $viewVars The view vars
     * @param string $expected The expected result
     * @return void
     */
    #[DataProvider('moduleIndexDefaultViewTypeProvider')]
    public function testModuleIndexDefaultViewType(array $viewVars, string $expected): void
    {
        $request = $response = $events = null;
        $name = (string)Hash::get($viewVars, 'currentModule.name', 'dummies');
        $data = compact('name');
        $view = new View($request, $response, $events, $data);
        foreach ($viewVars as $key => $value) {
            $view->set($key, $value);
        }
        $layout = new LayoutHelper($view);
        $actual = $layout->moduleIndexDefaultViewType();
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testModuleIndexViewType` test case.
     *
     * @return array
     */
    public static function moduleIndexViewTypeProvider(): array
    {
        return [
            'documents list' => [
                ['currentModule' => ['name' => 'documents']],
                [],
                'list',
            ],
            'folders tree' => [
                ['currentModule' => ['name' => 'folders']],
                [],
                'tree',
            ],
            'folders list' => [
                ['currentModule' => ['name' => 'folders']],
                ['view_type' => 'list'],
                'list',
            ],
        ];
    }

    /**
     * Test `moduleIndexViewType` method.
     *
     * @param array $viewVars The view vars
     * @param array $query The query params
     * @param string $expected The expected result
     * @return void
     */
    #[DataProvider('moduleIndexViewTypeProvider')]
    public function testModuleIndexViewType(array $viewVars, array $query, string $expected): void
    {
        $request = new ServerRequest(['query' => $query]);
        $response = $events = null;
        $name = (string)Hash::get($viewVars, 'currentModule.name', 'dummies');
        $data = compact('name');
        $view = new View($request, $response, $events, $data);
        foreach ($viewVars as $key => $value) {
            $view->set($key, $value);
        }
        $layout = new LayoutHelper($view);
        $actual = $layout->moduleIndexViewType();
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testModuleIndexViewTypes` test case.
     *
     * @return array
     */
    public static function moduleIndexViewTypesProvider(): array
    {
        return [
            'documents' => [
                ['currentModule' => ['name' => 'documents']],
                ['list'],
            ],
            'folders' => [
                ['currentModule' => ['name' => 'folders']],
                ['tree', 'list'],
            ],
        ];
    }

    /**
     * Test `moduleIndexViewTypes
     *
     * @param array $viewVars The view vars
     * @param array $expected The expected result
     * @return void
     */
    #[DataProvider('moduleIndexViewTypesProvider')]
    public function testModuleIndexViewTypes(array $viewVars, array $expected): void
    {
        $request = $response = $events = null;
        $name = (string)Hash::get($viewVars, 'currentModule.name', 'dummies');
        $data = compact('name');
        $view = new View($request, $response, $events, $data);
        foreach ($viewVars as $key => $value) {
            $view->set($key, $value);
        }
        $layout = new LayoutHelper($view);
        $actual = $layout->moduleIndexViewTypes();
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testTitle` test case.
     *
     * @return array
     */
    public static function titleProvider(): array
    {
        return [
            'empty' => [
                '',
                'Module',
                [],
            ],
            'only module' => [
                'objects',
                'Module',
                [
                    'currentModule' => ['name' => 'objects'],
                ],
            ],
            'objects' => [
                'Object title | objects',
                'Module',
                [
                    'object' => [
                        'attributes' => [
                            'title' => 'Object title',
                        ],
                    ],
                    'currentModule' => ['name' => 'objects'],
                ],
            ],
            'video' => [
                'Video title | video',
                'Module',
                [
                    'object' => [
                        'attributes' => [
                            'title' => 'Video title',
                        ],
                    ],
                    'currentModule' => ['name' => 'video'],
                ],
            ],
            'video html' => [
                'Video title | video',
                'Module',
                [
                    'object' => [
                        'attributes' => [
                            'title' => '<div><i>Video</i> <b>title</b></div>',
                        ],
                    ],
                    'currentModule' => ['name' => 'video'],
                ],
            ],
        ];
    }

    /**
     * Test `title` method
     *
     * @param string $expected The expected title
     * @param string $name The view name
     * @param array $viewVars The view vars
     */
    #[DataProvider('titleProvider')]
    public function testTitle(string $expected, string $name, array $viewVars = []): void
    {
        $request = $response = $events = null;
        $data = ['name' => $name];
        $view = new View($request, $response, $events, $data);
        foreach ($viewVars as $key => $value) {
            $view->set($key, $value);
        }
        $layout = new LayoutHelper($view);
        $result = $layout->title();
        static::assertSame($expected, $result);
    }

    /**
     * Test `tr` method
     *
     * @return void
     */
    public function testTranslation(): void
    {
        $view = new View();
        $view->set('currentModule', ['name' => 'documents']);
        $layout = new LayoutHelper($view);
        $expected = __('Objects');
        $actual = $layout->tr('Objects');
        static::assertSame($expected, $actual);

        Configure::write('Plugins', ['DummyPlugin' => ['bootstrap' => true, 'routes' => true, 'ignoreMissing' => true]]);
        $expected = __d('DummyPlugin', 'Objects');
        $actual = $layout->tr('Objects');
        static::assertSame($expected, $actual);
    }

    public static function publishStatusProvider(): array
    {
        return [
            'empty object' => [
                [],
                '',
            ],
            'expired' => [
                ['attributes' => ['publish_end' => '2022-01-01 00:00:00']],
                'expired',
            ],
            'future' => [
                ['attributes' => ['publish_start' => '2222-01-01 00:00:00']],
                'future',
            ],
            'locked' => [
                ['meta' => ['locked' => true]],
                'locked',
            ],
            'draft' => [
                ['attributes' => ['status' => 'draft']],
                'draft',
            ],
            'none of above' => [
                ['attributes' => ['title' => 'dummy']],
                '',
            ],
        ];
    }

    /**
     * Test `publishStatus` method
     *
     * @return void
     */
    #[DataProvider('publishStatusProvider')]
    public function testPublishStatus(array $object, string $expected): void
    {
        $view = new View();
        $layout = new LayoutHelper($view);
        $actual = $layout->publishStatus($object);
        static::assertSame($expected, $actual);
    }

    /**
     * Test `metaConfig` method
     *
     * @return void
     */
    public function testMetaConfig(): void
    {
        Cache::enable();
        $params = ['_csrfToken' => 'my-token'];
        $request = new ServerRequest(compact('params'));
        $viewVars = [
            'modules' => ['documents' => [], 'images' => []],
            'uploadable' => ['images'],
            'relationsSchema' => ['whatever'],
        ];
        $view = new View($request, null, null, compact('viewVars'));
        $layout = new LayoutHelper($view);
        $system = new SystemHelper($view);
        $conf = $layout->metaConfig();
        $expected = [
            'base' => '',
            'currentModule' => ['name' => 'home'],
            'template' => '',
            'modules' => ['documents', 'images'],
            'plugins' => Plugin::loadedAppPlugins(),
            'uploadable' => ['images'],
            'locale' => I18n::getLocale(),
            'csrfToken' => 'my-token',
            'maxFileSize' => $system->getMaxFileSize(),
            'canReadUsers' => false,
            'canSave' => true,
            'cloneConfig' => (array)Configure::read('Clone'),
            'placeholdersConfig' => $system->placeholdersConfig(),
            'uploadConfig' => $system->uploadConfig(),
            'relationsSchema' => ['whatever'],
            'richeditorConfig' => (array)Configure::read('Richeditor'),
            'richeditorByPropertyConfig' => (array)Configure::read('RicheditorByProperty'),
            'indexLists' => (array)$layout->indexLists(),
        ];
        static::assertSame($expected, $conf);
        Cache::disable();
    }

    /**
     * Test `metaConfig` method
     *
     * @return void
     */
    public function testMetaConfigToken(): void
    {
        $post = ['_csrfToken' => 'some-token'];
        $request = new ServerRequest(compact('post'));
        $view = new View($request);
        $layout = new LayoutHelper($view);
        $conf = $layout->metaConfig();
        static::assertSame('some-token', $conf['csrfToken']);
    }

    /**
     * Data provider for `testGetCsrfToken`
     *
     * @return array
     */
    public static function csrfTokenProvider(): array
    {
        $request = new ServerRequest();

        return [
            [
                '_csrfToken-from-request-params',
                new LayoutHelper(new View(new ServerRequest(['params' => ['_csrfToken' => '_csrfToken-from-request-params']]))),
            ],
            [
                '_csrfToken-from-request-data',
                new LayoutHelper(new View(new ServerRequest(['post' => ['_csrfToken' => '_csrfToken-from-request-data']]))),
            ],
            [
                'csrfToken-from-request-attribute',
                new LayoutHelper(new View($request->withAttribute('csrfToken', 'csrfToken-from-request-attribute'))),
            ],
            [
                'csrfToken-from-request-cookie',
                new LayoutHelper(new View($request->withCookieCollection(new CookieCollection([Cookie::create('csrfToken', 'csrfToken-from-request-cookie', [])])))),
            ],
            [
                null,
                new LayoutHelper(new View($request)),
            ],
        ];
    }

    /**
     * Test `getCsrfToken` method
     *
     * @param string|null $expected The expected result
     * @param \App\View\Helper\LayoutHelper $layout The layout helper
     * @return void
     */
    #[DataProvider('csrfTokenProvider')]
    public function testGetCsrfToken(?string $expected, LayoutHelper $layout): void
    {
        $actual = $layout->getCsrfToken();
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testTrashLink` test case.
     *
     * @return array
     */
    public static function trashLinkProvider(): array
    {
        return [
            'null' => [
                null,
                '',
            ],
            'empty' => [
                '',
                '',
            ],
            'trash' => [
                'trash',
                '',
            ],
            'translations' => [
                'translations',
                '',
            ],
            'not existing type' => [
                'notExistingType',
                '',
            ],
            'dummies' => [
                'dummies',
                '<a href="/trash?filter%5Btype%5D%5B0%5D=dummies" class="button icon icon-only-icon has-text-module-dummies" title="Dummies in Trashcan"><span class="is-sr-only">Trash</span><app-icon icon="carbon:trash-can"></app-icon></a>',
            ],
        ];
    }

    /**
     * Test `trashLink`.
     *
     * @param string|null $input The input
     * @param string $expected The expected result
     * @return void
     */
    #[DataProvider('trashLinkProvider')]
    public function testTrashLink(?string $input, string $expected): void
    {
        $viewVars = [
            'modules' => [
                'dummies' => [
                    'hints' => [
                        'object_type' => true,
                    ],
                ],
            ],
        ];
        $request = new ServerRequest();
        $view = new View($request, null, null, compact('viewVars'));
        $layout = new LayoutHelper($view);
        $actual = $layout->trashLink($input);
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testDashboardModuleLink` test case.
     *
     * @return array
     */
    public static function dashboardModuleLinkProvider(): array
    {
        return [
            'trash' => [
                'trash',
                [],
                '',
            ],
            'users' => [
                'users',
                [],
                '',
            ],
            'documents' => [
                'documents',
                [],
                '<a href="/documents" class="dashboard-item has-background-module-documents "><span>Documents</span><app-icon icon="carbon:document" :style="{ width: \'28px\', height: \'28px\' }"></app-icon></a>',
            ],
        ];
    }

    /**
     * Test `dashboardModuleLink`.
     *
     * @return void
     */
    #[DataProvider('dashboardModuleLinkProvider')]
    public function testDashboardModuleLink(string $name, array $module, string $expected): void
    {
        $modules = (array)Configure::read('Modules', []);
        $modules['test_items'] = ['icon' => 'test:items'];
        Configure::write('Modules', $modules);
        $viewVars = [];
        $request = new ServerRequest();
        $view = new View($request, null, null, compact('viewVars'));
        $layout = new LayoutHelper($view);
        $actual = $layout->dashboardModuleLink($name, $module);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testModuleIcon` test case.
     *
     * @return array
     */
    public static function moduleIconProvider(): array
    {
        return [
            'hints multiple types' => [
                'animals',
                [
                    'hints' => [
                        'multiple_types' => true,
                    ],
                ],
                '<app-icon icon="carbon:grid" :style="{ width: \'28px\', height: \'28px\' }"></app-icon>',

            ],
            'documents' => [
                'documents',
                [],
                '<app-icon icon="carbon:document" :style="{ width: \'28px\', height: \'28px\' }"></app-icon>',
            ],
            'from conf' => [
                'test_items',
                [],
                '<app-icon icon="test:items" :style="{ width: \'28px\', height: \'28px\' }"></app-icon>',
            ],
            'from map (core modules)' => [
                'locations',
                [],
                '<app-icon icon="carbon:location" :style="{ width: \'28px\', height: \'28px\' }"></app-icon>',
            ],
            'other module (non core, non conf)' => [
                'cats',
                [],
                '',
            ],
        ];
    }

    /**
     * Test `moduleIcon`.
     *
     * @param string $name The module name
     * @param array $module The module configuration
     * @param string $expected The expected result
     * @return void
     */
    #[DataProvider('moduleIconProvider')]
    public function testModuleIcon(string $name, array $module, string $expected): void
    {
        $modules = (array)Configure::read('Modules', []);
        $modules['test_items'] = ['icon' => 'test:items'];
        Configure::write('Modules', $modules);
        $viewVars = [];
        $request = new ServerRequest();
        $view = new View($request, null, null, compact('viewVars'));
        $layout = new LayoutHelper($view);
        $actual = $layout->moduleIcon($name, $module);
        static::assertEquals($expected, $actual);
    }

    /**
     * Test `moduleCount` method.
     *
     * @return void
     */
    public function testModuleCount(): void
    {
        $moduleName = 'test';
        $viewVars = [];
        $request = new ServerRequest();
        $view = new View($request, null, null, compact('viewVars'));
        $layout = new LayoutHelper($view);
        $actual = $layout->moduleCount($moduleName);
        static::assertEquals('<span class="module-count">-</span>', $actual);

        Cache::enable();
        $count = 42;
        CacheTools::setModuleCount(['meta' => ['pagination' => ['count' => $count]]], $moduleName);
        $expected = sprintf('<span class="module-count">%s</span>', $count);
        $actual = $layout->moduleCount($moduleName);
        static::assertEquals($expected, $actual);
        $actual = $layout->moduleCount($moduleName, 'my-dummy-css-class');
        $expected = sprintf('<span class="module-count">%s</span>', $count);
        static::assertEquals($expected, $actual);
        Cache::disable();
    }

    /**
     * Test `showCounter` method.
     *
     * @return void
     */
    public function testShowCounter(): void
    {
        Configure::delete('UI.modules.counters');
        $layout = new LayoutHelper(new View());
        // false
        $actual = $layout->showCounter('dummy');
        static::assertFalse($actual);
        // true
        $actual = $layout->showCounter('trash');
        static::assertTrue($actual);
        Configure::write('UI.modules.counters', 'all');
        $actual = $layout->showCounter('dummy');
        static::assertTrue($actual);
        Configure::delete('UI.modules.counters');
    }

    /**
     * Test `propertyGroup` method.
     *
     * @return void
     */
    public function testPropertyGroup(): void
    {
        $view = new View();
        $layout = new LayoutHelper($view);
        $view->set('properties', [
            'media' => [
                'provider' => '',
                'provider_uid' => '',
            ],
        ]);
        $actual = $layout->propertyGroup('whatever');
        static::assertNull($actual);
        $expected = 'media';
        $actual = $layout->propertyGroup('provider_uid');
        static::assertEquals($expected, $actual);
    }

    /**
     * Test `indexLists` method.
     *
     * @return void
     */
    public function testIndexLists(): void
    {
        Cache::enable();
        // read from cache
        $expected = ['whatever'];
        $key = CacheTools::cacheKey('properties.indexLists');
        Cache::write($key, $expected, 'default');
        $view = new View();
        $layout = new LayoutHelper($view);
        $actual = $layout->indexLists();
        static::assertEquals($expected, $actual);

        // read from config
        Cache::delete($key, 'default');
        $actual = $layout->indexLists();
        static::assertIsArray($actual);
        static::assertArrayHasKey('audio', $actual);
        static::assertArrayHasKey('categories', $actual);
        static::assertArrayHasKey('files', $actual);
        static::assertArrayHasKey('images', $actual);
        static::assertArrayHasKey('media', $actual);
        static::assertArrayHasKey('object_types', $actual);
        static::assertArrayHasKey('property_types', $actual);
        static::assertArrayHasKey('relations', $actual);
        static::assertArrayHasKey('tags', $actual);
        static::assertArrayHasKey('users', $actual);
        static::assertArrayHasKey('user_profile', $actual);
        static::assertArrayHasKey('videos', $actual);

        // remove API config, return empty array
        $apiConfig = Configure::read('API');
        Configure::write('API', []);
        $actual = $layout->indexLists();
        static::assertIsArray($actual);
        static::assertEmpty($actual);
        Configure::write('API', $apiConfig);

        Cache::disable();
    }
}
