<?php

namespace Iqbalatma\LaravelAudit;

use App\Enums\ImportName;
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
    public function setAction(string $action): self
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
    public function setMessage(string $message): self
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
    public function setObject(Model $model): self
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
    public function setAppName(string $appName): self
    {
        $this->appName = $appName;
        return $this;
    }

    /**
     * @param string $key
     * @param Collection|array $beforeChanges
     * @param Model|Collection $object
     * @param string $keyComparation
     * @return $this
     */
    public function addBeforeAfter(string $key, Collection|array $beforeChanges, Model|Collection $object, string $keyComparation = "id"): self
    {
        if ($beforeChanges instanceof Collection && $object instanceof Collection) {
            $this->before[$key] = collect();
            $this->after[$key] = collect();

            $beforeUpdateIds = $beforeChanges->pluck($keyComparation);
            $afterUpdateIds = $object->pluck($keyComparation);

            $intersectedIds = $beforeUpdateIds->intersect($afterUpdateIds);
            $intersectedBeforeCollection = $beforeChanges->whereIn("id", $intersectedIds)->values();
            $intersectedAfterCollection = $object->whereIn("id", $intersectedIds)->values();
            foreach ($intersectedBeforeCollection as $intersectedBefore) {
                $intersectedAfter = $intersectedAfterCollection->where("id", $intersectedBefore["id"])->first();

                if (!$intersectedAfter) {
                    continue;
                }

                if ($intersectedAfter instanceof Model) {
                    $intersectedAfter = $intersectedAfter->toArray();
                }

                if ($intersectedBefore instanceof Model) {
                    $intersectedBefore = $intersectedBefore->toArray();
                }

                $after = array_diff_assoc($intersectedAfter, $intersectedBefore);
                $before = array_intersect_key($intersectedBefore, array_flip(array_keys($after)));

                $this->before[$key]->push($before);
                $this->after[$key]->push($after);
            }


            $diffBefore = $beforeUpdateIds->diff($afterUpdateIds);
            $diffAfter = $afterUpdateIds->diff($beforeUpdateIds);

            if ($diffAfter->count() > 0 || $diffBefore->count() > 0) {
                $this->before[$key]->push(...$beforeChanges->whereIn("id", $diffBefore)->toArray());
                $this->after[$key]->push(...$object->whereIn("id", $diffAfter)->toArray());
            }
        } elseif (is_array($beforeChanges) && $object instanceof Model) {
            if (count($object->getChanges()) > 0) {
                $this->before->put($key, array_intersect_key($beforeChanges, $object->getChanges()));
                $this->after->put($key, $object->getChanges());
            }
        }

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
