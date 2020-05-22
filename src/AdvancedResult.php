<?php

namespace MinionFactory\ModelMapper;

use MinionFactory\ModelMapper\Contracts\ModelAttributeMapping;

/**
 * Class AdvancedResult
 * @package MinionFactory\ModelMapper;
 */
class AdvancedResult extends RawResult
{
    use ModelAttributeMapping;

    /**
     * The connection object used by one of the models.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connectionObject;

    /**
     * Set the connection associated with the model.
     *
     * @param  \Illuminate\Database\Connection $connection
     * @return $this
     */
    public function setConnectionObject($connection)
    {
        $this->connectionObject = $connection;

        return $this;
    }

    /**
     * Get the database connection for the model.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->connectionObject;
    }

    /**
     * @param $records
     * @param array $mapping
     * @param bool $single
     *
     * @return \Illuminate\Support\Collection
     */
    public static function make(&$records, $mapping = [], $single = false)
    {
        if ($single) {
            if ( ! $records || ! is_array($records) || ! count($records)) {
                return null;
            }
            $newRecord = $records[0];
            $records   = self::makeSingle($newRecord, $mapping);

            return $records;
        }

        if ( ! $records || ! is_array($records) || ! count($records)) {
            $records = [];
        }

        foreach ($records as $ix => $record) {
            $newRecord    = self::makeSingle($record, $mapping);
            $records[$ix] = $newRecord;
        }

        $records = collect($records);

        return $records;
    }

    private static function makeSingle($record, $mapping = [])
    {
        $newRecord             = new self();
        $newRecord->attributes = (array)$record;
        $newRecord->original   = (array)$record;

        if (sizeof($mapping)) {
            $newRecord->modelMapper($mapping);
        }

        return $newRecord;
    }
}