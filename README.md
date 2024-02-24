# LARAVEL AUDIT BY IQBAL ATMA MULIAWAN
Laravel Audit is a laravel package that use to track changes on your process. It will also track client ip address, http method, endpoint, user request, user agent, user that making changes, object that changes, before and after data changes. You need to set object to let audit know which model/data that being change. After that, you also need to set before and after data. 


## How to install ?
Here is how to install the package :
```console
composer require iqbalatma/laravel-audit
```

## How to publish vendor ?
You need to publish vendor (migration and config). Here is how to publish vendor :
```console
php artisan vendor:publish --provider="Iqbalatma\LaravelAudit\Providers\LaravelAuditServiceProvider"
```
