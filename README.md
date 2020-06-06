FidesioIsidoreBundle
======================

## Installation

### Step 1: Download FidesioIsidoreBundle using composer
```js
{
    "require": {
        "fidesio/isidore-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ composer update fidesio/isidore-bundle
```

### Step 2: Enable the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Fidesio\IsidoreBundle\FidesioIsidoreBundle(),
    );
}
```

## Basic Usage

``` yaml
# app/config/config.yml

fidesio_isidore:
    client:
        url: http://url.to.isidore.app # Isidore URL
        login: api_login # Isidore login
        password: api_password # Isidore api password
        auth_basic_user: auth_basic_user # Application Basic Authorization `user` if needed / not required
        auth_basic_pass: auth_basic_pass # Application Basic Authorization `password` if needed / not required
    cache:
        enable: true|false
        type: file|redis
        redis: redis://localhost:6379/3 # redis DSN
```
