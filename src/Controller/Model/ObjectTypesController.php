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
namespace App\Controller\Model;

use BEdita\SDK\BEditaClientException;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Utility\Hash;
use Psr\Log\LogLevel;

/**
 * Object Types Model Controller: list, add, edit, remove object types
 *
 * @property \App\Controller\Component\PropertiesComponent $Properties
 * @property \App\Controller\Component\SchemaComponent $Schema
 */
class ObjectTypesController extends ModelBaseController
{
    /**
     * Core tables list.
     *
     * @var array
     */
    public const TABLES = [
        'BEdita/Core.Folders',
        'BEdita/Core.Links',
        'BEdita/Core.Locations',
        'BEdita/Core.Media',
        'BEdita/Core.Objects',
        'BEdita/Core.Profiles',
        'BEdita/Core.Publications',
        'BEdita/Core.Users',
    ];

    /**
     * Resource type currently used
     *
     * @var string
     */
    protected $resourceType = 'object_types';

    /**
     * @inheritDoc
     */
    public function create(): ?Response
    {
        parent::create();
        $this->set('propertyTypesOptions', $this->Properties->typesOptions());

        return null;
    }

    /**
     * @inheritDoc
     */
    public function view($id): ?Response
    {
        parent::view($id);

        // retrieve additional data
        $resource = (array)$this->viewBuilder()->getVar('resource');
        $name = Hash::get($resource, 'attributes.name', 'undefined');
        $filter = ['object_type' => $name];
        try {
            $data = $this->propertiesData($filter);
        } catch (BEditaClientException $e) {
            $this->log($e->getMessage(), LogLevel::ERROR);
            $this->Flash->error($e->getMessage(), ['params' => $e]);

            return $this->redirect(['_name' => 'model:list:' . $this->resourceType]);
        }

        $objectTypeProperties = $this->prepareProperties($data, $name);
        $this->set(compact('objectTypeProperties'));
        $schema = $this->Schema->getSchema();
        $this->set('schema', $this->updateSchema($schema, $resource));
        $this->set('properties', $this->Properties->viewGroups($resource, $this->resourceType));
        $this->set('propertyTypesOptions', $this->Properties->typesOptions());
        $this->set('associationsOptions', $this->Properties->associationsOptions((array)Hash::get($resource, 'attributes.associations')));
        // setup `currentAttributes`
        $this->Modules->setupAttributes($resource);

        // get object schema
        $this->Schema->setConfig(['internalSchema' => false]);
        $this->set('objectTypeSchema', $this->Schema->getSchema($name));
        $this->Schema->setConfig(['internalSchema' => true]);

        return null;
    }

    /**
     * Read all properties via API, performing multiple calls if necessary.
     *
     * @param array $filter Properties filter
     * @return array
     */
    protected function propertiesData(array $filter): array
    {
        $data = [];
        $done = false;
        $page = 1;
        while (!$done) {
            $response = $this->apiClient->get(
                '/model/properties',
                compact('filter', 'page') + ['page_size' => 100]
            );
            $data = array_merge($data, (array)Hash::get($response, 'data'));
            $pageCount = (int)Hash::get($response, 'meta.pagination.page_count');
            $done = $pageCount === $page;
            $page++;
        }

        return $data;
    }

    /**
     * Update schema using resource.
     * If core type, skip.
     * Otherwise, set table and parent_name.
     *
     * @param array $schema The schema
     * @param array $resource The resource
     * @return array
     */
    protected function updateSchema(array $schema, array $resource): array
    {
        if ((bool)Hash::get($resource, 'meta.core_type')) {
            return $schema;
        }
        $schema['properties']['table'] = [
            'type' => 'string',
            'enum' => $this->tables($resource),
        ];
        $schema['properties']['parent_name'] = [
            'type' => 'string',
            'enum' => array_merge([''], $this->Schema->abstractTypes()),
        ];

        return $schema;
    }

    /**
     * Get available tables list
     *
     * @return array
     */
    protected function tables(array $resource): array
    {
        $tables = array_unique(
            array_merge(
                self::TABLES,
                (array)Configure::read('Model.objectTypesTables')
            )
        );
        $tables = array_unique(
            array_merge(
                $tables,
                (array)Hash::get($resource, 'attributes.table')
            )
        );
        sort($tables);

        return $tables;
    }

    /**
     * Separate properties between `inherited`, `core`  and `custom`
     *
     * @param array $data Property array
     * @param string $name Object type name
     * @return array
     */
    protected function prepareProperties(array $data, string $name): array
    {
        $map = [
            'core' => [],
            'inherited' => [],
            'custom' => [],
        ];
        foreach ($data as $prop) {
            $key = !is_numeric($prop['id']) ? ($prop['attributes']['object_type_name'] === $name ? 'core' : 'prop') : 'custom';
            $map[$key][] = $prop;
        }

        return [
            'core' => Hash::sort($map['core'], '{n}.attributes.name'),
            'inherited' => Hash::sort($map['inherited'], '{n}.attributes.name'),
            'custom' => Hash::sort($map['custom'], '{n}.attributes.name'),
        ];
    }

    /**
     * Save object type.
     *
     * @return \Cake\Http\Response|null
     */
    public function save(): ?Response
    {
        $this->addCustomProperties();
        $this->request = $this->request->withoutData('addedProperties');
        if ($this->request->getData('associations') === '') {
            $this->request = $this->request->withData('associations', null);
        }

        return parent::save();
    }

    /**
     * Add custom property
     *
     * @return void
     */
    protected function addCustomProperties(): void
    {
        $added = json_decode((string)$this->request->getData('addedProperties'), true);
        if (empty($added) || !is_array($added)) {
            return;
        }
        $objectTypeName = $this->request->getData('name');

        foreach ($added as $prop) {
            $data = [
                'type' => 'properties',
                'attributes' => [
                    'description' => Hash::get($prop, 'description'),
                    'name' => Hash::get($prop, 'name'),
                    'property_type_name' => Hash::get($prop, 'type'),
                    'object_type_name' => $objectTypeName,
                ],
            ];
            $this->apiClient->post('/model/properties', json_encode(compact('data')));
        }
    }
}
