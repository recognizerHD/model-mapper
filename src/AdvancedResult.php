<?php

namespace MinionFactory\ModelMapper;

use MinionFactory\ModelMapper\Contacts\ModelAttributeMapping;

/**
 * Class AdvancedResult
 * @package MinionFactory\ModelMapper;
 */
class AdvancedResult extends RawResult
{
    use ModelAttributeMapping;

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
            return collect(null);
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