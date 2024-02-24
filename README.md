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

$product = Product::create([
  "name"     => "laptop",
  "category" => "digital",
  "price"    => 10000000
]);

$audit = AuditService::init(); #initiate object

#all of this value is optional
$audit->setAction("ADD_NEW_DATA")
      ->setMessage("add new data product with category #digitcal")
      ->setTag(["#product"])
      ->setAdditional(["this could be meta data or something else"])
      ->setObject($product)
      ->setAppName("Product App") #in case you will use this package in multiple project and sharing database, you can defined the record from which app
      ->addBefore("product", null) #data before process, key product is null
      ->addAfter("product", $product); #data after process, the key of product is not null
      ->log(); #you can also write before after here log(["product" => null], ["product" => $product]), first parameter as data before, and second parameter as data after
#if your code in single function, you can just use log, without addAfter and addBefore
#but if your code in separate function, you can use that method to append key of collection
#you can also add Collection, string, array as second parameter of addAfter and addBefore
#
?>

```
