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
    /**
     * @param string $key
     * @param Collection|array $beforeChanges
     * @param Model|Collection $object
     * @param string $keyComparation
     * @return $this
     * @deprecated use addBeforeAfterSingleEntity for single entity and
     * addBeforeAfterAttachDetachCollection for sync collection one to many or many to many
     */
    public function addBeforeAfter(string $key, Collection|array $beforeChanges, Model|Collection $object, string $keyComparation = "id"): self
    {
        if ($beforeChanges instanceof Collection && $object instanceof Collection) {
            $beforeUpdateIds = $beforeChanges->pluck($keyComparation);
            $afterUpdateIds = $object->pluck($keyComparation);


            $diffBefore = $beforeUpdateIds->diff($afterUpdateIds);
            $diffAfter = $afterUpdateIds->diff($beforeUpdateIds);

            if ($diffAfter->count() > 0 || $diffBefore->count() > 0) {
                $this->before->put($key, $beforeChanges->toArray());
                $this->after->put($key, $object->toArray());
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
     * @param string $key
     * @param Collection|array $beforeChanges
     * @param Model|Collection $object
     * @param string $keyComparation
     * @return $this
     */
    public function addBeforeAfterSingleEntity(string $key, Model|array $beforeChanges, Model|array $afterChanges): self
    {
        if ($beforeChanges instanceof Model) {
            $beforeChanges = $beforeChanges->toArray();
        }

        if ($afterChanges instanceof Model) {
            $afterChanges = $afterChanges->toArray();
        }

        $differences = array_diff_assoc($beforeChanges, $afterChanges);
        $targetKeys = array_keys($differences);

        if (count($targetKeys) > 0) {
            $this->before->put($key, array_intersect_key($beforeChanges, array_flip($targetKeys)));
            $this->after->put($key, array_intersect_key($afterChanges, array_flip($targetKeys)));
        }


        return $this;
    }

    /**
     * @param string $key
     * @param Collection|array $beforeChanges
     * @param Model|Collection $object
     * @param string $keyComparation
     * @return $this
     */
    public function addBeforeAfterAttachDetachCollection(string $key, Collection $beforeChanges, Collection $afterChanges, string $keyComparation = "id"): self
    {
        $beforeUpdateIds = $beforeChanges->pluck($keyComparation);
        $afterUpdateIds = $afterChanges->pluck($keyComparation);


        $diffBefore = $beforeUpdateIds->diff($afterUpdateIds);
        $diffAfter = $afterUpdateIds->diff($beforeUpdateIds);

        if ($diffAfter->count() > 0 || $diffBefore->count() > 0) {
            $this->before->put($key, $beforeChanges->pluck("id")->toArray());
            $this->after->put($key, $afterChanges->pluck("id")->toArray());
        }
        return $this;
    }

    public function addBeforeAfterCollectionChanges(string $key, Collection $beforeChanges, Collection $afterChanges, string $keyComparassion = "id"): self
    {
        if ($beforeChanges->count() > 0) {
            $this->before[$key] = collect();
            $this->after[$key] = collect();

            $beforeUpdateIds = $beforeChanges->pluck($keyComparassion);
            $afterUpdateIds = $afterChanges->pluck($keyComparassion);

            $intersectedIds = $beforeUpdateIds->intersect($afterUpdateIds);

            $intersectedBeforeCollection = $beforeChanges->whereIn($keyComparassion, $intersectedIds)->values();
            $intersectedAfterCollection = $afterChanges->whereIn($keyComparassion, $intersectedIds)->values();

            foreach ($intersectedBeforeCollection as $intersectedBefore) {
                $intersectedAfter = $intersectedAfterCollection->where($keyComparassion, $intersectedBefore[$keyComparassion])->first();

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

                $this->before[$key]->push(array_merge([$keyComparassion => $intersectedBefore[$keyComparassion]], $before));
                $this->after[$key]->push(array_merge([$keyComparassion => $intersectedAfter[$keyComparassion]], $after));
            }

            $existingCollection = $this->before[$key]->pluck($keyComparassion);
            $newCollectionItem = $afterChanges->whereNotIn($keyComparassion, $existingCollection)->values();

            if ($newCollectionItem->count() > 0) {
                $this->after[$key] = $this->after[$key]->merge($newCollectionItem);
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
