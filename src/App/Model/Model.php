<?php

namespace Jupitern\Slim3\App\Model;

use App\Model\MongoModel;
use Respect\Validation\Validator;

class Model
{
    protected $storageNaame = '';
    protected $primaryKeys = [];

    protected $storageType = 'default';
    protected $attributes = [];
    protected $fillable = [];
    protected $hidden = [];
    protected $useDateFields = true;


    public function __construct()
    {
        $this->attributes = array_fill_keys($this->attributes, null);
        if ($useDateFields) {
            $this->attributes["datecreated"] = null;
            $this->attributes["dateupdated"] = null;
        }
    }


    /**
     * @param bool $all
     * @return array
     */
    public function getAttributes(bool $all = false)
    {
        if ($all) {
            return $this->attributes;
        }

        return array_diff_key($this->attributes, array_fill_keys($this->hidden, ''));
    }

    /**
     * @param array   $attributes
     * @param boolean $safe
     * @return $this
     */
    public function setAttributes($attributes, $safe = false)
    {
        if ($safe) {
            $attributes = array_intersect_key((array)$attributes, $this->attributes);
        } else {
            $attributes = array_intersect_key((array)$attributes, array_flip($this->fillable));
        }

        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }


    /**
     * set scenario
     * @param string $scenario
     * @return \Jupitern\Slim3\App\Model\Model
     */
    public function setScenario(string $scenario = "default"): MongoModel
    {
        $this->scenario = $scenario;

        return $this;
    }

    /**
     * Get Validator
     * @param string $scenario
     * @return \Respect\Validation\Validator
     */
    public function getValidator(): Validator
    {
        return new \Respect\Validation\Validator;
    }


    /**
     * @param bool      $validate
     * @param bool|null $isNew
     * @return mixed
     */
    public function save(bool $validate = true, ?bool $isNew = null): mixed
    {

    }


    public function delete(): bool
    {

    }


    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get(string $name): mixed
    {
        if (!key_exists($name, $this->attributes)) {
            throw new \Exception("Property $name does not exist on model " . __CLASS__);
        }

        return $this->attributes[$name];
    }

    /**
     * @param string    $name
     * @param mixed     $value
     * @return void
     * @throws \Exception
     */
    public function __set(string $name, mixed $value) :void
    {
        if (!key_exists($name, $this->attributes)) {
            throw new \Exception("Property $name does not exist on model " . __CLASS__);
        }

        $this->attributes[$name] = $value;
    }


    /**
     * @param string    $name
     * @return bool
     * @throws \Exception
     */
    public function __isset(string $name) : bool
    {
        return isset($this->attributes[$name]);
    }

}