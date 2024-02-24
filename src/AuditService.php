<?php

namespace Iqbalatma\LaravelAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Iqbalatma\LaravelAudit\Abstracts\BaseAuditService;
use Iqbalatma\LaravelAudit\Jobs\AuditJob;

class AuditService extends BaseAuditService
{
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
    public function setAction(string $action):self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param array $tag
     * @return $this
     */
    public function setTag(array $tag): self
    {
        $this->tag = array_merge($this->tag, $tag);
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message):self
    {
        $this->message = $message;
        return $this;
    }


    /**
     * @param array $additional
     * @return $this
     */
    public function setAdditional(array $additional): self
    {
        $this->additional = array_merge($this->additional, $additional);
        return $this;
    }


    /**
     * @return $this
     */
    public function setObject(Model $model):self
    {
        $this->objectTable = $model->getTable();
        $this->objectId = $model->getKey();
        return $this;
    }


    /**
     * @param string $key
     * @param array|string|Collection|Model|null $before
     * @return $this
     */
    public function addBefore(string $key, array|string|null|Collection|Model $before): self
    {
        $this->before->put($key, $before);

        return $this;
    }


    /**
     * @param string $key
     * @param array|string|Collection|Model|null $after
     * @return $this
     */
    public function addAfter(string $key, array|string|null|Collection|Model $after): self
    {
        $this->after->put($key, $after);
        return $this;
    }

    /**
     * @param string $appName
     * @return $this
     */
    public function setAppName(string $appName):self
    {
        $this->appName = $appName;
        return $this;
    }

    /**
     * @param array|string|Collection|Model|null $before
     * @param array|string|Collection|Model|null $after
     * @return void
     */
    public function log(array|string|null|Collection|Model $before = [], array|string|null|Collection|Model $after = []): void
    {
        $this->before = $this->before->merge($before);
        $this->after = $this->after->merge($after);


        if ($this->after->count() > 0 || $this->before->count() > 0) {
            AuditJob::dispatch($this);
        }
    }
}
