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

After publishing migration, you can run the migration.
```console
php artisan migrate
```

## How to use ?
First you need to initiate the audit service. After that, you can set all data with available method.

```php
<?php

use Iqbalatma\LaravelAudit\AuditService;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

$product = Product::create([
  "name"     => "laptop",
  "category" => "digital",
  "price"    => 10000000
]);

$audit = AuditService::init(); #initiate object
#or
$audit = AuditService::init(action: "CREATE_PRODUCT", message: "Create product via method abc in class xyz", entryObject: $product, guard: "web", user: Auth::user());

#all of this value is optional
AuditService::init("CREATE_PRODUCT", "Create product via method abc in class xyz")
    ->setEntryObject($product) #required, and become optional if you already set via init
    ->addSingleTrail($role, null, $role->toArray()) #required
    ->setAppName("E-Commerce") #optional
    ->setTag(["level" => "important"]) #optional
    ->setAdditional(["role" => "ADMIN"]) #optional
    ->setActor(Auth::user()) #optional
    ->execute();
?>

```
