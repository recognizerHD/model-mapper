<?php

namespace MinionFactory\ModelMapper;

use Illuminate\Database\Eloquent\Model;
use MinionFactory\ModelMapper\Contracts\ModelAttributeMapping;

/**
 * Class AdvancedModel
 * @package MinionFactory\ModelMapper;
 */
class AdvancedModel extends Model
{
    use ModelAttributeMapping;

    // TODO handle UTC and local dates with this.
    protected $utcDates = [
        self::UPDATED_AT,
        self::CREATED_AT
    ];

    /**
     * @param array $fillable
     */
    public function addFillable(array $fillable)
    {
        $this->fillable = array_merge($fillable, $this->fillable);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        $result = parent::getAttribute($key);
        if ($result !== null) {
            return $result;
        }

        // One last check on the parent value.
        return $this->getParentValue($key);
    }
}