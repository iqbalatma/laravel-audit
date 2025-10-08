<?php
if (! function_exists('audit_model')) {
    /**
     * Returning class of audit model
     *
     * @return string
     */
    function audit_model(): string
    {
        return config('laravel_audit.audit_model');
    }
}

if (! function_exists('getDefaultUser'))
{
    /**
     * Returning class of audit model
     *
     * @return Model|User
     */

    function getDefaultUser(): Model|User
    {
        return  User::where('username', config("default_audit_actor.username"))
            ->firstOrFail();
    }
}
