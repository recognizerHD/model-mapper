<?php

namespace MinionFactory\ModelMapper\Contracts;

use MinionFactory\ModelMapper\AdvancedResult;

trait ModelAttributeMapping
{
    protected $modelAttributeMapping = [];
    protected $foreignModels = [];
    protected $modelIndex = [];
    protected $parentObject = null;

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->modelAttributeMapping[$key])) {
            $model = $this->modelAttributeMapping[$key];
            if ( ! isset($this->foreignModels[$model])) {
                try {
                    $this->foreignModels[$model] = new $model;
                    if ($this instanceof AdvancedResult && ! $this->connectionObject) {
                        $connection = $this->foreignModels[$model]->getConnection();
                        $this->setConnectionObject($connection);
                    }
                    if (method_exists($this->foreignModels[$model], 'setParentObject')) {
                        $this->foreignModels[$model]->setParentObject($this);
                    }
                } catch (\Exception $e) {
                    // Model doesn't exist. lets return parent.
                    return $this->getParentValue($key);
                }

                foreach ($this->modelIndex[$model] as $field) {
                    if (method_exists($this->foreignModels[$model], 'setRawAttribute')) {
                        $this->foreignModels[$model]->setRawAttribute($field, $this->getAttributeFromArray($field));
                    } else {
                        $this->foreignModels[$model]->$field = $this->getParentValue($field);
                    }
                }
            }

            // We found that the local attribute is different from the foreign model version. Update the local to match.
            if ($this->foreignModels[$model]->$key ?? null !== null) {
                if (parent::getAttribute($key) !== $this->foreignModels[$model]->$key) {
                    if (method_exists($this->foreignModels[$model], 'setRawAttribute')) {
                        $this->setRawAttribute($key, $this->foreignModels[$model]->$key);
                    } else {
                        $this->setAttribute($key, $this->foreignModels[$model]->$key);
                    }
                }
            }

            return $this->foreignModels[$model]->$key ?? null;
        }

        return $this->getParentValue($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  string  $value
     *
     * @return mixed
     */
    public function __set($key, $value)
    {
        if (isset($this->modelAttributeMapping[$key])) {
            $model = $this->modelAttributeMapping[$key];
            if ( ! isset($this->foreignModels[$model])) {
                try {
                    $this->foreignModels[$model] = new $model;
                } catch (\Exception $e) {
                    // Model doesn't exist. lets return parent.
                    return $this->setAttribute($key, $value);
                }

                foreach ($this->modelIndex[$model] as $field) {
                    if ($field == $key) {
                        continue;
                    } // We are setting this in a few lines.
                    if (method_exists($this->foreignModels[$model], 'setRawAttribute')) {
                        $this->foreignModels[$model]->setRawAttribute($field, parent::getAttributeFromArray($field));
                    } else {
                        $this->foreignModels[$model]->$field = parent::getAttribute($field);
                    }
                }
            }

            $this->foreignModels[$model]->$key = $value;

            // Because the mutator on the model may have altered the value, lets change the local attribute to match.
            if (method_exists($this->foreignModels[$model], 'setRawAttribute')) {
                return parent::setRawAttribute($key, $this->foreignModels[$model]->$key);
            } else {
                return $this->setAttribute($key, $this->foreignModels[$model]->$key);
            }
        }

        return $this->setAttribute($key, $value);
    }

    /**
     * @param  array  $array
     */
    public function modelMapper($array)
    {
        $actualMapping = [];
        foreach ($array as $field => $class) {
            switch (strtolower($class)) {
                case "integer":
                case "int":
                    $this->setRawAttribute($field, intval($this->getAttributeValue($field)));
                    break;
                case "boolean":
                case "bool":
                    $this->setRawAttribute($field, boolval($this->getAttributeValue($field)));
                    break;
                case "float":
                    $this->setRawAttribute($field, floatval($this->getAttributeValue($field)));
                    break;
                case "double":
                    $this->setRawAttribute($field, doubleval($this->getAttributeValue($field)));
                    break;
                case "string":
                case "text":
                    $this->setRawAttribute($field, strval($this->getAttributeValue($field)));
                    break;
                default:
                    if ( ! isset($this->modelIndex[$class])) {
                        $this->modelIndex[$class] = [];
                    }
                    $this->modelIndex[$class][] = $field;
                    $actualMapping[$field] = $class;
            }
        }
        $this->modelAttributeMapping = $actualMapping;
    }

    public function setParentObject(&$parent)
    {
        $this->parentObject = $parent;
    }

    private function getParentValue($key)
    {
        if (isset($this->parentObject) && $this->parentObject && ! key_exists($key, $this->attributes)) {
            return $this->parentObject->$key;
        }

        return parent::getAttribute($key);
    }

//attributes start
    public function setRawAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }
//attributes end
}
