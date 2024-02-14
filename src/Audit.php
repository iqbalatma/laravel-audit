<?php

namespace Iqbalatma\LaravelAudit;

class Audit
{
    protected string|null $message;
    protected string|null $action;
    protected string|null $ipAddress;
    protected string|null $endpoint;
    protected string|null $method;
    protected string|null $userAgent;
    protected string|null $actorTable;
    protected string|null $actorId;
    protected string|null $actorName;
    protected string|null $actorEmail;
    protected string|null $actorPhone;
    protected string|null $objectTable;
    protected string|null $objectId;
    protected string|null $trail;
    protected string|null $tag;
    protected string|null $additionalData;
    protected string|null $appName;

    public function __construct()
    {
        $this->setNetwork();

    }

    /**
     * @return self
     */
    public static function init(): self
    {
        return new static();
    }


    /**
     * @return $this
     */
    protected function setNetwork():self
    {
        $this->method = request()?->getMethod();
        $this->ipAddress = request()?->getClientIp();
        $this->userAgent = request()?->header("user-agent");
        $this->endpoint = parse_url(request()?->url())["path"] ?? null;
        return $this;
    }
}
