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
        password: api_paasword # Isidore api password
```
