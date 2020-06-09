# Seed-PHP Microframework

## Rate Limit

>Package: `Helper\RateLimit` <br >
>Namespace: `SeedPHP\Helper`

Gives you a simple class to help managing and throttling calls to an API.

#### Example

```php
  $config = [ ... ]; #see description below
  $app->load('ratelimit', $config);
  $app->ratelimit->test();
```

When loading this helper without the `$config` options, it will be attached to your `App` with the default options. However, you can customize its options as follows:

Key | Type | Default | Remark
--- | ---- | ------- | ------
limit_per_second | `int` | `2` | How many requests are accepted per second.
limit_per_minute | `int` | It's dynamically calculated and will be `70%` of the `limit_per_second` times 60 seconds. <br><br > E.g. when limiting 2 calls per second, this will become `84`.  | How many requests are accepted per minute.
limit_per_hour | `int` | It's dynamically calculated and will be `50%` of the `limit_per_minute` times 60 minutes. <br><br > E.g. when limiting 84 calls per minute, this will become `2,520`. | How many requests are accepted per hour.
seconds_banned | `int` | Starts on `600` (10 minutes) and automatically double aways a ban is applied.  | Defines for how long the API should be unavailable after exceeding the limits. 
seconds_delay | `int` | `3`| Defines how many seconds are gonna be applied for throttling (delaying) requests that either reaches 80% of the given `limit_per_hour` or exceeds more than 5 times in a single session. 

---

### <span style="color: #42b983;">#</span> test(): void

Test the call, throttling or denying it depending on the circunstances. Ideally, this should be placed at the very begining of your code, possibly right after the `SeedPHP\App::getInstance()`, as seen below:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = SeedPHP\App::getInstance();

$app->load('ratelimit');
$app->ratelimit->test();

// ...

$app->run();

```

Also, it can be defined per route, such as:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = SeedPHP\App::getInstance();

$app->load('ratelimit');

$app->route('GET /', function () use ($app) {
    $app->ratelimit->test();

    // ... 
});

// ...

$app->run();

```

##### Arguments

- `None`

##### Return

- `void`


