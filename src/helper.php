<?php
if (! function_exists('audit_model')) {
    /**
     * Returning class of audit model
     *
     * @return string
     */
    function audit_model(): string
    {
        return  config('auditelasticsearch.audit_model');
    }
}
