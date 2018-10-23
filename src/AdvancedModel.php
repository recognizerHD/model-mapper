<?php

namespace Tomahawk\ModelMapper;

use Illuminate\Database\Eloquent\Model;
use Tomahawk\ModelMapper\Contacts\ModelAttributeMapping;

/**
 * Class AdvancedModel
 * @package Tomahawk\ModelMapper;
 *
 * For usage see https://wiki.ops-com.com/x/S4QCAw
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
     * @return string
     */
    public function getDateFormat()
    {
        if ($this->getConnection()->getConfig('driver') === 'sqlsrv') {
            return 'Y-m-d H:i:s.u';
        } else {
            return parent::getDateFormat();
        }
    }

    /**
     * @param \DateTime|int $value
     *
     * @return bool|string
     */
    public function fromDateTime($value)
    {
        if ($this->getConnection()->getConfig('driver') === 'sqlsrv') {
            return substr(parent::fromDateTime($value), 0, -3);
        } else {
            return parent::fromDateTime($value);
        }
    }
}