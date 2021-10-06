<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

namespace mmaurice\parser\trustpilot\components;

use \Closure;

class Registry
{
    protected $storage;

    public function __construct()
    {
        $this->flush();
    }

    public function get($key, $defaultValue = null)
    {
        if ($this->has($key)) {
            if ($this->storage[$key] instanceof Closure) {
                return $this->storage[$key]->__invoke();
            }

            return $this->storage[$key];
        }

        return $defaultValue;
    }

    public function set($key, $value)
    {
        $this->storage[$key] = $value;

        return $this;
    }

    public function has($key)
    {
        if (array_key_exists($key, $this->storage)) {
            return true;
        }

        return false;
    }

    public function flush()
    {
        $this->storage = [];

        return $this;
    }
}
