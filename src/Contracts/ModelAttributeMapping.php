<?php

namespace MinionFactory\ModelMapper\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use MinionFactory\ModelMapper\AdvancedResult;

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
                    $actualMapping[$field]      = $class;
            }
        }
        $this->modelAttributeMapping = $actualMapping;
    }

    public function setRawAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
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
     * @param string $key
     * @param string $value
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

    private function getParentValue($key)
    {
        if (isset($this->parentObject) && $this->parentObject && ! key_exists($key, $this->attributes)) {
            return $this->parentObject->$key;
        }

        return parent::getAttribute($key);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        // If an attribute is listed as a "date", we'll convert it from a DateTime
        // instance into a form proper for storage on the database tables using
        // the connection grammar's date format. We will auto set the values.
        elseif ($value && $this->isUtcDateAttribute($key)) {
            $value = $this->fromUtcDateTime($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute is a date or date castable.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isUtcDateAttribute($key)
    {
        return in_array($key, $this->getUtcDates(), true) ||
               $this->isDateCastable($key);
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getUtcDates()
    {
        return $this->utcDates ?? [];
    }

    /**
     * Convert a DateTime to a storable string.
     *
     * @param mixed $value
     *
     * @return string|null
     */
    public function fromUtcDateTime($value)
    {
        return empty($value) ? $value : $this->asUtcDateTime($value)->format(
            $this->getDateFormat()
        );
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asUtcDateTime($value, $returnTimezone = 'UTC')
    {
//        $returnTimezone = ($this->utcAsLocal ?? true) ? date_default_timezone_get() : 'UTC';
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof CarbonInterface) {
            return Date::instance($value)->setTimezone($returnTimezone);
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return Date::parse(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            )->setTimezone($returnTimezone);
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Date::createFromTimestamp($value)->setTimezone($returnTimezone);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if ($this->isStandardDateFormat($value)) {
            return Date::instance(Carbon::createFromFormat('Y-m-d', $value, new \DateTimeZone('UTC'))->startOfDay())->setTimezone($returnTimezone);
        }

        $format = $this->getDateFormat();

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        if (Date::hasFormat($value, $format)) {
            return Date::createFromFormat($format, $value, new \DateTimeZone('UTC'))->setTimezone($returnTimezone);
        }

        return Date::parse($value, new \DateTimeZone('UTC'))->setTimezone($returnTimezone);
    }

    public function setParentObject(&$parent)
    {
        $this->parentObject = $parent;
    }

    /**
     * Transform a raw model value using mutators, casts, etc.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function transformModelValue($key, $value)
    {
        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependent upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if ($value !== null
            && \in_array($key, $this->getUtcDates(), false)) {
            return $this->asUtcDateTime($value, ($this->utcAsLocal ?? true) ? date_default_timezone_get() : 'UTC');
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if ($value !== null
            && \in_array($key, $this->getDates(), false)) {
            return $this->asDateTime($value);
        }

        return $value;
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        $defaults = [
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];

        return $this->usesTimestamps()
            ? array_unique(array_merge($this->dates, $this->utcDates ?? [], $defaults))
            : array_unique(array_merge($this->dates, $this->utcDates ?? []));
    }
}
