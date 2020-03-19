<?php
namespace ff\database;

use ff;

class userModel
{
    public $inited = false;
    public $attributes = [];
    public $instance = null;

    public function __construct($isInstance = true)
    {
        if ($isInstance) {
            $this->instance = ff::createObject(__CLASS__, [false]);
        }
    }

    public function init($uid = null, $token = null)
    {

        if (is_null($uid) || is_null($token)) {
            return null;
        }

        $attributes = (new \models\tables\User)->getAttributesByUid($uid);

        if (isset($attributes['id'])) {
            $attributes['uid'] = $attributes['id'];
        }

        if (isset($attributes['token']) && $attributes['token'] == $token) {
            unset($attributes['password']);

            $this->instance->attributes = $attributes;
            $this->instance->inited = true;

            return $attributes;

        } else {
            return null;
        }

    }

    public function asArray()
    {
        return $this->instance->attributes;
    }

    public function __get($name)
    {
        return $this->instance->attributes[$name] ?? null;
    }
}
