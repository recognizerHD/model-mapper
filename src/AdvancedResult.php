<?php

namespace MinionFactory\ModelMapper;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
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
     * @var Connection|null
     */
    protected ?Connection $connectionObject = null;

    /**
     * @param $records
     * @param  array|null  $mapping
     * @param  bool  $single
     *
     * @return AdvancedResult|Collection|null
     */
    public static function make(&$records, ?array $mapping = [], ?bool $single = false): self|Collection|null
    {
        if ($single) {
            if ( ! $records || ! is_array($records) || ! count($records)) {
                return null;
            }
            $newRecord = $records[0];
            $records = self::makeSingle($newRecord, $mapping);

            return $records;
        }

        if ( ! $records || ! is_array($records) || ! count($records)) {
            $records = [];
        }

        foreach ($records as $ix => $record) {
            $newRecord = self::makeSingle($record, $mapping);
            $records[$ix] = $newRecord;
        }

        $records = collect($records);

        return $records;
    }

    /**
     * Get the database connection for the model.
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connectionObject;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  Connection  $connection
     *
     * @return $this
     */
    public function setConnectionObject(Connection $connection): static
    {
        $this->connectionObject = $connection;

        return $this;
    }

    private static function makeSingle($record, $mapping = []): self
    {
        $newRecord = new self();
        $newRecord->attributes = (array) $record;
        $newRecord->original = (array) $record;

        if (sizeof($mapping)) {
            $newRecord->modelMapper($mapping);
        }

        return $newRecord;
    }
}
