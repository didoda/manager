<?php
namespace App\Test\TestCase\Controller\Administration;

use App\Controller\Administration\AsyncJobsController;
use BEdita\WebTools\ApiClientProvider;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * Test class
 *
 * @uses \App\Controller\Administration\AsyncJobsController
 */
class JobsController extends AsyncJobsController
{
    protected $resourceType = 'async_jobs';
    protected $properties = ['name'];
}

/**
 * {@see \App\Controller\Administration\AsyncJobsController} Test Case
 *
 * @coversDefaultClass \App\Controller\Administration\AsyncJobsController
 */
class AsyncJobsControllerTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Test\TestCase\Controller\AsyncJobsController
     */
    public $AsyncJobsController;

    /**
     * Test request config
     *
     * @var array
     */
    public $defaultRequestConfig = [
        'environment' => [
            'REQUEST_METHOD' => 'GET',
        ],
        'params' => [
            'resource_type' => 'async_jobs',
        ],
    ];

    /**
     * API client
     *
     * @var BEditaClient
     */
    protected $client;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $config = array_merge($this->defaultRequestConfig, []);
        $request = new ServerRequest($config);
        $this->AsyncJobsController = new JobsController($request);
        $this->client = ApiClientProvider::getApiClient();
        $adminUser = getenv('BEDITA_ADMIN_USR');
        $adminPassword = getenv('BEDITA_ADMIN_PWD');
        $response = $this->client->authenticate($adminUser, $adminPassword);
        $this->client->setupTokens($response['meta']);
    }

    /**
     * Basic test
     *
     * @return void
     */
    public function testBase(): void
    {
        $this->AsyncJobsController->index();
        $keys = [
            'resources',
            'meta',
            'links',
            'resourceType',
            'properties',
            'metaColumns',
            'filter',
            'schema',
            'readonly',
        ];
        $viewVars = (array)$this->AsyncJobsController->viewVars;
        foreach ($keys as $expectedKey) {
            static::assertArrayHasKey($expectedKey, $viewVars);
        }
        static::assertEquals('async_jobs', $viewVars['resourceType']);
        static::assertEquals(['name'], $viewVars['properties']);
    }
}
