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
    /**
     * @var array
     * @deprecated This will be removed in a future version. This package will no longer handle UTC dates.
     *  TODO IMPORTANT Remove this in a future version.
     */
    protected $utcDates = [
        self::UPDATED_AT,
        self::CREATED_AT,
    ];

    /**
     * @var bool
     * @deprecated This will be removed in a future version. This package will no longer handle UTC dates.
     *  TODO IMPORTANT Remove this in a future version.
     */
    protected $utcAsLocal = true;

    /**
     * @param  array  $fillable
     */
    public function addFillable(array $fillable)
    {
        $this->fillable = array_merge($fillable, $this->fillable);
    }

//attributes start

    /**
     * @param  string  $key
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
//attributes end
}
