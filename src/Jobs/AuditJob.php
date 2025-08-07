<?php

namespace Iqbalatma\LaravelAudit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Iqbalatma\LaravelAudit\AuditService;
use Iqbalatma\LaravelAudit\AuditService2;
use Iqbalatma\LaravelAudit\Model\Audit;
use JsonException;
use Throwable;

class AuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public AuditService $audit)
    {
    }

    /**
     * Execute the job.
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(): void
    {
        if ($this->audit->trails->count() > 0) {
            DB::beginTransaction();

            /** @var Audit $auditModel */
            $auditModel =
            config( 'laravel_audit.audit_model')::query()->create([
                    "message" => $this->audit->message,
                    "action" => $this->audit->action,
                    "ip_address" => $this->audit->ipAddress,
                    "endpoint" => $this->audit->endpoint,
                    "method" => $this->audit->method,
                    "user_agent" => $this->audit->userAgent,
                    "user_request" => json_encode($this->audit->userRequest, JSON_THROW_ON_ERROR),
                    "actor_table" => $this->audit->actorTable,
                    "actor_id" => $this->audit->actorId,
                    "actor_name" => $this->audit->actorName,
                    "actor_email" => $this->audit->actorEmail,
                    "actor_phone" => $this->audit->actorPhone,
                    "entry_object_table" => $this->audit->entryObjectTable,
                    "entry_object_id" => $this->audit->entryObjectId,
                    "tag" => json_encode($this->audit->tag, JSON_THROW_ON_ERROR),
                    "additional" => json_encode($this->audit->additional, JSON_THROW_ON_ERROR),
                    "app_name" => $this->audit->appName,
                ]);


            foreach ($this->audit->trails as $trail) {
                $auditModel->audit_trails()->create([
                    "object_table" => $trail["object_table"],
                    "object_id" => $trail["object_id"] ?? null,
                    "before" => $trail["before"],
                    "after" => $trail["after"],
                    "tag" => $trail["tag"],
                    "additional" => $trail["additional"],
                ]);
            }

            DB::commit();
        }


    }
}
