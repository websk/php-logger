<?php

namespace WebSK\Logger\Entry;

use WebSK\Entity\Entity;
use WebSK\Entity\ProtectPropertiesTrait;

/**
 * Class LoggerEntry
 * @package WebSK\Logger\Entry
 */
class LoggerEntry extends Entity
{
    use ProtectPropertiesTrait;

    const DB_TABLE_NAME = 'logger_entry';

    const _USER_FULL_ID = 'user_full_id';
    protected ?string $user_full_id = null;

    const _OBJECT_FULL_ID = 'object_full_id';
    protected string $object_full_id;

    const _SERIALIZED_OBJECT = 'serialized_object';
    protected string $serialized_object;

    const _USER_IP = 'user_ip';
    protected string $user_ip;

    const _COMMENT = 'comment';
    protected string $comment;

    const _REQUEST_URI_WITH_SERVER_NAME = 'request_uri_with_server_name';
    protected string $request_uri_with_server_name = '';

    const _HTTP_USER_AGENT = 'http_user_agent';
    protected string $http_user_agent = '';

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $value
     */
    public function setComment(string $value)
    {
        $this->comment = $value;
    }

    /**
     * @return string
     */
    public function getUserIp(): string
    {
        return $this->user_ip;
    }

    /**
     * @param string $value
     */
    public function setUserIp(string $value)
    {
        $this->user_ip = $value;
    }

    /**
     * @return string
     */
    public function getSerializedObject(): string
    {
        return $this->serialized_object;
    }

    /**
     * @param string $value
     */
    public function setSerializedObject(string $value)
    {
        $this->serialized_object = $value;
    }

    /**
     * @return string
     */
    public function getObjectFullId(): string
    {
        return $this->object_full_id;
    }

    /**
     * @param string $value
     */
    public function setObjectFullId(string $value)
    {
        $this->object_full_id = $value;
    }

    /**
     * @return null|string
     */
    public function getUserFullId(): ?string
    {
        return $this->user_full_id;
    }

    /**
     * @param null|string $value
     */
    public function setUserFullId(?string $value)
    {
        $this->user_full_id = $value;
    }

    /**
     * @return string
     */
    public function getRequestUriWithServerName(): string
    {
        return $this->request_uri_with_server_name;
    }

    /**
     * @param string $request_uri_with_server_name
     */
    public function setRequestUriWithServerName(string $request_uri_with_server_name): void
    {
        $this->request_uri_with_server_name = $request_uri_with_server_name;
    }

    /**
     * @return string
     */
    public function getHttpUserAgent(): string
    {
        return $this->http_user_agent;
    }

    /**
     * @param string $http_user_agent
     */
    public function setHttpUserAgent(string $http_user_agent): void
    {
        $this->http_user_agent = $http_user_agent;
    }
}
