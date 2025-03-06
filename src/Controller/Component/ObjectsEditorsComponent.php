<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2022 Atlas Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace App\Controller\Component;

use Cake\Cache\Cache;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * ObjectsEditors component
 *
 * @property-read \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class ObjectsEditorsComponent extends Component
{
    /**
     * Components
     *
     * @var array
     */
    protected array $components = ['Authentication'];

    /**
     * Objects editors.
     *
     * @var array
     */
    public array $objectsEditors;

    /**
     * Concurrent check time.
     *
     * @var int
     */
    public int $concurrentCheckTime = 20000;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        // set concurrent check time by config, if any. default 20s
        $concurrentCheckTime = Configure::read('Editors.concurrentCheckTime');
        if (!empty($concurrentCheckTime)) {
            $this->concurrentCheckTime = $concurrentCheckTime;
        }
        // get concurrent editors
        $this->objectsEditors = $this->getEditors();
        // remove expired data
        $this->cleanup();

        parent::initialize($config);
    }

    /**
     * Update objects editors adding the user access to ID object
     *
     * @param string $id The object ID
     * @return string
     */
    public function update(string $id): string
    {
        if (!array_key_exists($id, $this->objectsEditors)) {
            $this->objectsEditors[$id] = [];
        }
        $found = false;
        $editor = $this->editorName();
        $now = time();
        foreach ($this->objectsEditors[$id] as &$objectEditor) {
            if ($objectEditor['name'] === $editor) {
                $found = true;
                $objectEditor['timestamp'] = $now;
                break;
            }
        }
        if (!$found) {
            $this->objectsEditors[$id][] = [
                'name' => $editor,
                'timestamp' => $now,
            ];
        }
        $encoded = json_encode($this->objectsEditors);
        Cache::write('objects_editors', $encoded);

        return $encoded;
    }

    /**
     * Get editor name from authenticated user.
     *
     * @return string|null
     */
    public function editorName(): ?string
    {
        /** @var \Authentication\Identity|null $user */
        $user = $this->Authentication->getIdentity();
        if (empty($user)) {
            return null;
        }

        $name = (string)Hash::get($user, 'attributes.name');
        $surname = (string)Hash::get($user, 'attributes.surname');
        if (!empty($name) && !empty($surname)) {
            return sprintf('%s %s', $name, $surname);
        }
        $username = (string)Hash::get($user, 'attributes.username');
        if (!empty($username)) {
            return $username;
        }

        return null;
    }

    /**
     * Get all objects concurrent editors.
     *
     * @return array
     */
    public function getEditors(): array
    {
        $cached = (string)Cache::read('objects_editors', 'default');
        if (empty($cached)) {
            return [];
        }

        return json_decode($cached, true);
    }

    /**
     * Remove expired concurrent editors
     *
     * @return void
     */
    public function cleanup(): void
    {
        if (empty($this->objectsEditors)) {
            return;
        }
        $validity = $this->concurrentCheckTime / 1000;
        $now = time();
        foreach ($this->objectsEditors as $id => $objectEditors) {
            foreach ($objectEditors as $key => $objectEditor) {
                if ($objectEditor['timestamp'] + $validity < $now) {
                    unset($this->objectsEditors[$id][$key]);
                }
            }
        }
        Cache::write('objects_editors', json_encode($this->objectsEditors));
    }
}
