<?php

namespace Iqbalatma\LaravelAudit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Iqbalatma\LaravelAudit\AuditService;
use JsonException;

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
     */
    public function handle(): void
    {
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
            "object_table" => $this->audit->objectTable,
            "object_id" => $this->audit->objectId,
            "tag" => json_encode($this->audit->tag, JSON_THROW_ON_ERROR),
            "additional" => json_encode($this->audit->additional, JSON_THROW_ON_ERROR),
            "app_name" => $this->audit->appName,
            "trail" => json_encode([
                "before" => $this->audit->before,
                "after" => $this->audit->after,
            ], JSON_THROW_ON_ERROR),
        ]);
    }
}
