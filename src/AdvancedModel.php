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

    // This appears to be working for sqlsrv now.
//    /**
//     * @return string
//     */
//    public function getDateFormat()
//    {
//        if ($this->getConnection()->getConfig('driver') === 'sqlsrv') {
//            return 'Y-m-d H:i:s.u';
//        } else {
//            return parent::getDateFormat();
//        }
//    }
//
//    /**
//     * @param \DateTime|int $value
//     *
//     * @return bool|string
//     */
//    public function fromDateTime($value)
//    {
//        if ($this->getConnection()->getConfig('driver') === 'sqlsrv') {
//            return substr(parent::fromDateTime($value), 0, -3);
//        } else {
//            return parent::fromDateTime($value);
//        }
//    }
}