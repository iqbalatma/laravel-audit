<?php

namespace Iqbalatma\LaravelAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
    protected array|null $tag;
    protected array|null $additional;
    protected string|null $appName;
    protected Collection $before;
    protected Collection $after;

    public function __construct()
    {
        if (method_exists(Auth::user(), "getRoleNames") && config("laravel_audit.is_role_from_spatie")) {
            $this->additional = ["actor_role" => Auth::user()?->getRoleNames()->toArray()];
        } else {
            $this->additional = [];
        }
        $this->message = "";
        $this->action = "";
        $this->tag = [];
        $this->before = collect();
        $this->after = collect();

        $this->objectTable = null;
        $this->objectId = null;
        $this->appName = config("laravel_audit.app_name");

        $this->setNetwork()
            ->setActor();
    }

    /**
     * @return self
     */
    public static function init(): self
    {
        return new static();
    }

    /**
     * @param string $action
     * @return $this
     */
    public function action(string $action):self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param array $tag
     * @return $this
     */
    public function tag(array $tag): self
    {
        $this->tag = array_merge($this->tag, $tag);
        return $this;
    }


    /**
     * @param array $additional
     * @return $this
     */
    public function additional(array $additional): self
    {
        $this->additional = array_merge($this->additional, $additional);
        return $this;
    }


    /**
     * @param string $message
     * @return $this
     */
    public function message(string $message):self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return $this
     */
    public function object(Model $model):self
    {
        $this->objectTable = $model->getTable();
        $this->objectId = $model->getKey();
        return $this;
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


    /**
     * @return $this
     */
    protected function setActor():self
    {
        $user = Auth::user();

        $this->actorTable = $user?->getTable();
        $this->actorId = $user?->getKey();
        $this->actorEmail = $user?->{config("laravel_audit.actor_key.email")};
        $this->actorPhone = $user?->{config("laravel_audit.actor_key.phone")};
        $this->actorName = $user?->{config("laravel_audit.actor_key.name")};

        return $this;
    }

    /**
     * @param string $key
     * @param array|string|null $before
     * @return $this
     */
    public function addBefore(string $key, array|string|null $before): self
    {
        $this->before->put($key, $before);

        return $this;
    }


    /**
     * @param string $key
     * @param array|string|null $after
     * @return $this
     */
    public function addAfter(string $key, array|string|null $after): self
    {
        $this->after->put($key, $after);
        return $this;
    }

    /**
     * @param string $appName
     * @return $this
     */
    public function appName(string $appName):self
    {
        $this->appName = $appName;
        return $this;
    }

    /**
     * @param array $before
     * @param array $after
     * @return void
     */
    public function log(array $before = [], array $after = []): void
    {
        $this->before = $this->before->merge($before);
        $this->after = $this->after->merge($after);

        if (count($this->after) > 0 || count($this->before) > 0) {
//            AuditJob::dispatch($this);
        }
    }
}
