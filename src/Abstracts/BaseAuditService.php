<?php

namespace Iqbalatma\LaravelAudit\Abstracts;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Iqbalatma\LaravelAudit\Model\User;

abstract class BaseAuditService
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
    public string|null $objectTable;
    public string|null $objectId;
    public array|null $tag;
    public array|null $additional;
    public string|null $appName;
    public Collection $before;
    public Collection $after;

    public function __construct()
    {
        $this->additional = Auth::user() && method_exists(Auth::user(), "getRoleNames") && config("laravel_audit.is_role_from_spatie") ?
            ["actor_role" => Auth::user()?->getRoleNames()->toArray()] :
            [];

        $this->message = "";
        $this->action = "";

        $this->tag = [];
        $this->userRequest = [];

        $this->before = collect();
        $this->after = collect();

        $this->actorEmail = null;
        $this->actorName = null;
        $this->actorPhone = null;

        $this->objectTable = null;
        $this->objectId = null;
        $this->appName = config("laravel_audit.app_name");

        $this->setNetwork()
            ->setActor();
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

    private function filterRequest($collection)
    {
        return $collection->map(function($item) {
            // Jika item adalah array, panggil rekursif ke sub-array
            if (is_array($item)) {
                return $this->filterRequest(collect($item));  // Rekursif
            }

            // Jika item bukan objek UploadedFile, kembalikan item
            if (!($item instanceof \Illuminate\Http\UploadedFile)) {
                return $item;
            }

            // Jika item adalah file, kembalikan null
            return null;
        })->filter(function($item) {
            // Menghapus item yang bernilai null (yang merupakan file)
            return $item !== null;
        });
    }


    /**
     * @return $this
     */
    protected function setActor(): self
    {
        $user = Auth::user() ?? getDefaultUser();

        $this->actorTable = $user?->getTable();
        $this->actorId = $user?->getKey();

        if (config("laravel_audit.actor_key.email")) {
            $this->actorEmail = $user?->{config("laravel_audit.actor_key.email")};
        }

        if (config("laravel_audit.actor_key.phone")) {
            $this->actorPhone = $user?->{config("laravel_audit.actor_key.phone")};
        }

        if (config("laravel_audit.actor_key.name")) {
            $this->actorName = $user?->{config("laravel_audit.actor_key.name")};
        }

        return $this;
    }
}
