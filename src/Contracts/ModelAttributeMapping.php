<?php

namespace MinionFactory\ModelMapper\Contacts;

trait ModelAttributeMapping
{
    protected $modelAttributeMapping = [];
    protected $foreignModels = [];
    protected $modelIndex = [];
    protected $parentObject = null;

    /**
     * @param array $array
     */
    public function modelMapper($array)
    {
        $this->modelAttributeMapping = $array;
        foreach ($array as $field => $class) {
            if ( ! isset($this->modelIndex[$class])) {
                $this->modelIndex[$class] = [];
            }
            $this->modelIndex[$class][] = $field;
        }
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $key
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
                    if (method_exists($this->foreignModels[$model], 'setParentObject')) {
                        $this->foreignModels[$model]->setParentObject($this);
                    }
                } catch (\Exception $e) {
                    // Model doesn't exist. lets return parent.
                    return $this->getParentValue($key);
                }

                foreach ($this->modelIndex[$model] as $field) {
                    $this->foreignModels[$model]->$field = $this->getParentValue($field);
                }
            }

            // We found that the local attribute is different from the foreign model version. Update the local to match.
            if ($this->foreignModels[$model]->$key ?? null !== null) {
                if (parent::getAttribute($key) !== $this->foreignModels[$model]->$key) {
                    $this->setAttribute($key, $this->foreignModels[$model]->$key);
                }
            }

            return $this->foreignModels[$model]->$key ?? null;
        }

        return $this->getParentValue($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $key
     * @param  string $value
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
                    return parent::setAttribute($key, $value);
                }

                foreach ($this->modelIndex[$model] as $field) {
                    if ($field == $key) {
                        continue;
                    } // We are setting this in a few lines.
                    $this->foreignModels[$model]->$field = parent::getAttribute($field);
                }
            }

            $this->foreignModels[$model]->$key = $value;

            // Because the mutator on the model may have altered the value, lets change the local attribute to match.
            return parent::setAttribute($key, $this->foreignModels[$model]->$key);
        }

        return parent::setAttribute($key, $value);
    }

    private function getParentValue($key)
    {
        if (isset($this->parentObject) && $this->parentObject && ! key_exists($key, $this->attributes)) {
            return $this->parentObject->$key;
        }

        return parent::getAttribute($key);
    }

    public function setParentObject(&$parent)
    {
        $this->parentObject = $parent;
    }
}