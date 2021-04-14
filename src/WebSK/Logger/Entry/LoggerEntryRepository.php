<?php

namespace WebSK\Logger\Entry;

use WebSK\Utils\Sanitize;
use WebSK\Entity\EntityRepository;

class LoggerEntryRepository extends EntityRepository
{

    /**
     * @param int $current_entry_id
     * @param string $current_entry_full_id
     * @return int
     * @throws \Exception
     */
    public function getPrevRecordEntryId(int $current_entry_id, string $current_entry_full_id): int
    {
        $prev_record_id = $this->db_service->readField(
            "SELECT " . LoggerEntry::_ID . " FROM " . LoggerEntry::DB_TABLE_NAME
            . " WHERE " . LoggerEntry::_ID . " < ? AND " . LoggerEntry::_OBJECT_FULL_ID . " = ? 
                ORDER BY id DESC LIMIT 1",
            [$current_entry_id, $current_entry_full_id]
        );

        return (int)$prev_record_id;
    }

    /**
     * @param \DateTime $min_created_datetime
     * @param int $limit
     */
    public function removePastEntries(\DateTime $min_created_datetime, int $limit)
    {
        $db_table_name = $this->getTableName();

        $query = 'DELETE FROM ' . Sanitize::sanitizeSqlColumnName($db_table_name)
            . ' WHERE ' . Sanitize::sanitizeSqlColumnName(LoggerEntry::_CREATED_AT_TS) . '<=?'
            . ' AND ' . Sanitize::sanitizeSqlColumnName(LoggerEntry::_USER_FULL_ID) . ' IS NULL'
            . ' LIMIT ' . abs($limit);

        $where_arr = [$min_created_datetime->format('U')];

        $this->db_service->query($query, $where_arr);
    }
}
