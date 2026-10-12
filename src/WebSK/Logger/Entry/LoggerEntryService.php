<?php

namespace WebSK\Logger\Entry;

use WebSK\Entity\EntityService;
use WebSK\Entity\InterfaceEntity;
use WebSK\Utils\FullObjectId;
use WebSK\Utils\Network;

/**
 * Class LoggerEntryService
 * @package WebSK\Logger\Entry
 */
class LoggerEntryService extends EntityService
{
    public function getById(?int $entity_id, bool $exception_if_not_loaded = true): ?LoggerEntry
    {
        $entity = parent::getById($entity_id, $exception_if_not_loaded);
        if ($entity === null) {
            return null;
        }
        if (!$entity instanceof LoggerEntry) {
            throw new \UnexpectedValueException('Logger repository returned an incompatible entity');
        }

        return $entity;
    }

    /**
     * @param int $current_entry_id
     * @return int
     * @throws \Exception
     */
    public function getPrevRecordEntryId(int $current_entry_id): int
    {
        $current_entry = $this->getById($current_entry_id);
        if ($current_entry === null) {
            throw new \UnexpectedValueException('Current logger entry was not found');
        }

        return $this->getLoggerEntryRepository()->getPrevRecordEntryId(
            $current_entry_id,
            $current_entry->getObjectFullId()
        );
    }

    /**
     * Сохранение объекта
     * Каждый сохраняемый объект запоминаем, если запрашивается сохранение с таким же object_full_id
     * то смотрим есть ли изменения по сравнению с уже сохраненным.
     * Если нет, то не сохраняем. Если есть, то переписываем уже сохраненный
     * @param InterfaceEntity $object
     * @param string $object_full_id
     * @param string $comment
     * @param string|null $user_full_id
     * @throws \Exception
     */
    protected function logObjectAndId(
        InterfaceEntity $object,
        string $object_full_id,
        string $comment,
        ?string $user_full_id
    ): void {
        $ip_address = Network::getClientIpRemoteAddr();

        /** @var LoggerEntry[] $saved_entries_arr */
        static $saved_entries_arr = [];

        $serialized_object = serialize($object);

        if (!array_key_exists($object_full_id, $saved_entries_arr)) {
            $new_entry_obj = new LoggerEntry();
            $new_entry_obj->setUserIp($ip_address);
            $new_entry_obj->setUserFullid($user_full_id);
            $new_entry_obj->setObjectFullid($object_full_id);
            $new_entry_obj->setSerializedObject($serialized_object);
            $new_entry_obj->setComment($comment);
            $server_name = $_SERVER['SERVER_NAME'] ?? '';
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            $http_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $new_entry_obj->setRequestUriWithServerName(
                (is_string($server_name) ? $server_name : '') . (is_string($request_uri) ? $request_uri : '')
            );
            $new_entry_obj->setHttpUserAgent(is_string($http_user_agent) ? $http_user_agent : '');

            $this->save($new_entry_obj);

            $saved_entries_arr[$object_full_id] = $new_entry_obj;
        } else {
            $saved_entry_obj = $saved_entries_arr[$object_full_id];
            if ($serialized_object != $saved_entry_obj->getSerializedObject()) {
                $saved_entry_obj->setSerializedObject($serialized_object);

                $this->save($saved_entry_obj);

                $saved_entries_arr[$object_full_id] = $saved_entry_obj;
            }
        }
    }

    /**
     * @param InterfaceEntity $object
     * @param string $comment
     * @param null|string $user_full_id
     * @throws \Exception
     */
    public function logObjectEvent(InterfaceEntity $object, string $comment, ?string $user_full_id): void
    {
        $object_full_id = FullObjectId::getFullObjectId($object);
        if ($object_full_id === null) {
            throw new \UnexpectedValueException('Could not build the logged object identifier');
        }

        $this->logObjectAndId($object, $object_full_id, $comment, $user_full_id);
    }

    /**
     * @param \DateTime $min_created_datetime
     * @param int $limit
     */
    public function removePastLoggerEntries(\DateTime $min_created_datetime, int $limit): void
    {
        $this->getLoggerEntryRepository()->removePastEntries($min_created_datetime, $limit);
    }

    /**
     * @param LoggerEntry $logger_entry
     * @return int|null
     */
    public function getUserIdForEntry(LoggerEntry $logger_entry): ?int
    {
        $user_fullid_str = $logger_entry->getUserFullId();
        if ($user_fullid_str === null) {
            return null;
        }

        $user_full_id_arr = explode('.', $user_fullid_str);
        $user_id = $user_full_id_arr[1] ?? null;
        if (!is_string($user_id) || !ctype_digit($user_id) || (int)$user_id < 1) {
            return null;
        }

        return (int)$user_id;
    }

    private function getLoggerEntryRepository(): LoggerEntryRepository
    {
        if (!$this->repository instanceof LoggerEntryRepository) {
            throw new \LogicException('Logger entry service requires LoggerEntryRepository');
        }

        return $this->repository;
    }
}
