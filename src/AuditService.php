<?php

namespace Iqbalatma\LaravelAudit;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Iqbalatma\LaravelAudit\Jobs\AuditJob;
use RuntimeException;

class AuditService
{
    public string|null $message;
    public string|null $action;
    public string|null $ipAddress;
    public string|null $endpoint;
    public string|null $method;
    public string|null $userAgent;
    public Collection|array|null $userRequest;
    public string|null $actorTable;
    public string|null $actorId;
    public string|null $actorName;
    public string|null $actorEmail;
    public string|null $actorPhone;
    public string|null $entryObjectTable;
    public string|null $entryObjectId;
    public array|null $tag;
    public array|null $additional;
    public string|null $appName;
    public Collection $trails;
    /** @var Model|Authenticatable */
    public Model|null $user;

    public function __construct(
        string $action = "",
        string $message = "",
        Model  $user = null
    )
    {
        $this->user = null;
        $this->actorId = null;
        $this->actorEmail = null;
        $this->actorTable = null;
        $this->actorName = null;
        $this->actorPhone = null;
        // check user class model
        if ($user) {
            $userModel = config("laravel_audit.user_model");
            if ($userModel && $user instanceof $userModel) {
                $this->user = $user;
            } else {
                throw new RuntimeException("User class must be an instance of $userModel");
            }
        }
        $this->additional = $this->user && method_exists($this->user, "getRoleNames") && config("laravel_audit.is_role_from_spatie") ?
            ["actor_role" => $this->user?->getRoleNames()->toArray()] :
            [];

        $this->message = $message;
        $this->action = $action;

        $this->tag = [];
        $this->userRequest = [];


        $this->actorEmail = null;
        $this->actorName = null;
        $this->actorPhone = null;

        $this->entryObjectTable = null;
        $this->entryObjectId = null;
        $this->appName = config("laravel_audit.app_name");
        $this->trails = collect();
        $this->setNetwork();
        $this->setActor($this->user);
    }

    public static function init(
        string $action = "",
        string $message = "",
    ): self
    {
        return new static($action, $message);
    }


    /**
     * @return $this
     */
    protected function setNetwork(): self
    {
        $this->method = request()?->getMethod();
        $this->ipAddress = request()?->getClientIp();
        $this->userAgent = request()?->header("user-agent");
        $this->userRequest = $this->filterRequest(collect(request()->all()))->toArray();
        $this->endpoint = parse_url(request()?->url())["path"] ?? null;
        return $this;
    }

    /**
     * @param $collection
     * @return mixed
     */
    private function filterRequest($collection): Collection
    {
        return $collection->map(function ($item) {
            // if item is array, call recursive to sub-array
            if (is_array($item)) {
                return $this->filterRequest(collect($item));  // recursive
            }

            // if item is not object UploadedFile, return item
            if (!($item instanceof \Illuminate\Http\UploadedFile)) {
                return $item;
            }

            // if item is file, return null
            return null;
        })->filter(function ($item) {
            // delete item  null (because of file)
            return $item !== null;
        });
    }

    /**
     * @return $this
     */
    protected function setActor(Model|null $user): self
    {
        $user = $user ?? Auth::user();
        if ($user) {
            $this->actorTable = $user->getTable();
            $this->actorId = $user->getKey();

            if (config("laravel_audit.actor_key.email")) {
                $this->actorEmail = $user->{config("laravel_audit.actor_key.email")};
            }

            if (config("laravel_audit.actor_key.phone")) {
                $this->actorPhone = $user->{config("laravel_audit.actor_key.phone")};
            }

            if (config("laravel_audit.actor_key.name")) {
                $this->actorName = $user->{config("laravel_audit.actor_key.name")};
            }
        }

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
     * @return $this
     */
    public function setEntryObject(Model $model): self
    {
        $this->entryObjectTable = $model->getTable();
        $this->entryObjectId = $model->getKey();
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
     * @param array $additional
     * @return $this
     */
    public function setAdditional(array $additional): self
    {
        $this->additional = array_merge($this->additional, $additional);
        return $this;
    }

    /**
     * @throws \JsonException
     */
    public function addSingleTrail(Model $model, array|null $before = null, array|null $after = null, array $tag = [], array $additional = []): self
    {
        if (is_null($before) && is_null($after)) {
            $this->trails->push([
                "object_id" => $model->getKey(),
                "object_table" => $model->getTable(),
                "before" => $before,
                "after" => $after,
                "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
                "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
            ]);
            return $this;
        }

        if (is_null($before) && is_array($after)) {
            $this->trails->push([
                "object_id" => $model->getKey(),
                "object_table" => $model->getTable(),
                "before" => $before,
                "after" => json_encode($after, JSON_THROW_ON_ERROR),
                "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
                "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
            ]);
            return $this;
        }

        if (is_null($after) && is_array($before)) {
            $this->trails->push([
                "object_id" => $model->getKey(),
                "object_table" => $model->getTable(),
                "before" => json_encode($before, JSON_THROW_ON_ERROR),
                "after" => $after,
                "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
                "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
            ]);
            return $this;
        }

        $diffBefore = [];
        $diffAfter = [];

        # find key that different between before and after (exist in before but does not exist in after and so forth)
        $diffKeyBefore = array_diff_key($before, $after);
        $diffKeyAfter = array_diff_key($after, $before);
        $diffKeys = array_merge($diffKeyBefore + $diffKeyAfter);

        foreach ($before as $key => $beforeValue) {
            $afterValue = $after[$key] ?? null;

            if (
                !is_string($beforeValue) && !is_null($beforeValue) && !is_numeric($beforeValue) && !is_bool($beforeValue) &&
                !is_string($afterValue) && !is_null($afterValue) && !is_numeric($afterValue) && !is_bool($afterValue)
            ) { #skip iterable
                continue;
            }
            if ($afterValue !== $beforeValue) {
                $diffBefore[$key] = $beforeValue;
                $diffAfter[$key] = $afterValue;
            }
        }

        #if there are diff keys, looping 2 sideways
        if ($diffKeys) {
            foreach ($after as $key => $afterValue) {
                $beforeValue = $before[$key] ?? null;

                if (
                    !is_string($beforeValue) && !is_null($beforeValue) && !is_numeric($beforeValue) && !is_bool($beforeValue) &&
                    !is_string($afterValue) && !is_null($afterValue) && !is_numeric($afterValue) && !is_bool($afterValue)
                ) { #skip iterable
                    continue;
                }

                if ($beforeValue !== $afterValue) {
                    $diffBefore[$key] = $beforeValue;
                    $diffAfter[$key] = $afterValue;
                }
            }
        }

        if (empty($diffBefore) && empty($diffAfter)) {
            return $this;
        }

        $this->trails->push([
            "object_id" => $model->getKey(),
            "object_table" => $model->getTable(),
            "before" => json_encode($diffBefore, JSON_THROW_ON_ERROR),
            "after" => json_encode($diffAfter, JSON_THROW_ON_ERROR),
            "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
            "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
        ]);
        return $this;
    }

    /**
     * @param string|null $table
     * @param string|null $id
     * @param array $before
     * @param array $after
     * @param array $tag
     * @param array $additional
     * @return $this
     * @throws \JsonException
     */
    public function addRelationalTrail(string $table = null, array|null $before = null, array|null $after = null, array $tag = [], array $additional = []): self
    {
        if (is_null($before) && is_null($after)) {
            $this->trails->push([
                "object_table" => $table,
                "before" => $before,
                "after" => $after,
                "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
                "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
            ]);
            return $this;
        }

        if (is_null($before) && is_array($after)) {
            $this->trails->push([
                "object_table" => $table,
                "before" => $before,
                "after" => json_encode($after, JSON_THROW_ON_ERROR),
                "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
                "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
            ]);
            return $this;
        }

        if (is_null($after) && is_array($before)) {
            $this->trails->push([
                "object_table" => $table,
                "before" => json_encode($before, JSON_THROW_ON_ERROR),
                "after" => $after,
                "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
                "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
            ]);
            return $this;
        }

        $beforeMap = collect($before)->keyBy('b_id');
        $afterMap = collect($after)->keyBy('b_id');

        $diffBefore = [];
        $diffAfter = [];

        $allIds = $beforeMap->keys()->merge($afterMap->keys())->unique();
        foreach ($allIds as $b_id) {
            $beforeItem = $beforeMap->get($b_id);
            $afterItem = $afterMap->get($b_id);

            if ($beforeItem !== $afterItem) {
                if ($beforeItem !== null) {
                    $diffBefore[] = $beforeItem;
                }
                if ($afterItem !== null) {
                    $diffAfter[] = $afterItem;
                }
            }
        }

        if (empty($diffBefore) && empty($diffAfter)) {
            return $this;
        }

        $this->trails->push([
            "object_table" => $table,
            "before" => json_encode($diffBefore, JSON_THROW_ON_ERROR),
            "after" => json_encode($diffAfter, JSON_THROW_ON_ERROR),
            "tag" => json_encode($tag, JSON_THROW_ON_ERROR),
            "additional" => json_encode($additional, JSON_THROW_ON_ERROR),
        ]);
        return $this;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        AuditJob::dispatch($this);
    }
}
