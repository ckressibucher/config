Configuration Library
======================

[![Build Status](https://travis-ci.org/ckressibucher/config.svg)](https://travis-ci.org/ckressibucher/config)

This package provides a class to manage *hierarchical data*.
A common use case is configuration data.

Note that this package does not provide any readers or writers
for config files of different formats.
Instead it wraps php arrays
and *provides easier access to its values*.

Why?
----

I was tired of using too many `isset` throgh all my code. The main
advantage of this library is, that you can request
an arbitrary deeply nested value, and provide a default value which
should be returned in case the requested value does not exist:

```php
$myConfig = [
  'root' => [
    'specific' => ['key' => 'val'],
  ],
];

// plain php is a bit ugly...
$theSpecific = isset($myConfig['root']['specific']['key']) ?
     $myConfig['root']['specific']['key'] : null;

$theGeneral = isset($myConfig['root']['key']) ?
     $myConfig['root']['key'] : false;

// ... but with a `Config`
$config = new Ckr\Config\Config($myConfig);
$theSpecific = $config->get('root/specific/key');
$theGeneral = $config->get('root/key', false);
```

Actually, if you're using PHP7, this problem is solved:

```php
// valid code in PHP7
$theSpecific= $myConfig['root']['specific']['key'] ?? null;
$theGeneral = $myConfig['root']['key'] ?? false;
```

However, as I prefer to specify the path as `root/specific/key` instead
of `['root']['specific']['key']`, I still use this library in my
PHP7 projects.

Usage
-------

You start with a hierarchical data structure, defined as multi dimensional array:

```php
$myConfig = [
  'mode' => 'production',
  'logging' => [
    'factory' => 'Your\\Logging\\Factory',
    'loggers' => [
      [
        'type' => 'file',
        'path' => '/path/to/logs',
        'level' => 'warn',
      ],
      [
        'type' => 'email',
        'addr' => 'someone@somewhere.com',
        'level' => 'critical',
      ]
    ],
  ]
];
```

Then you can wrap it in a `Config` object:

```
use Ckr\Config\Config;

$c = new Config($myConfig);
```

You can ask for a simple value with `get`:

```php
$mode = $c->get('mode', 'dev');
```

In the example above, "dev" is the default mode. It is returned, if the key 'mode'
does not exist in the config data.

You can ask for a value nested in deeper dimensions:

```php
$loggingFactoryClass = $c->get('logging/factory');
```

If any of the used path parts don't exist, the default value (`null` if not specified)
is returned.

Say you want to use all of the logging data, e.g. to instantiate your logging instance.
For this, you can get a child config object:

```php
$loggingConfig = $c->child('logging'); /* @var $loggingConfig Config */

$myLoggingFactory = new $loggingFactoryClass;
$logger = $myLoggingFactory->create($loggingConfig);

// the logging factory..
public function create(Config $cfg)
{
  $loggers = $cfg->get('loggers'); // $loggers is an array
  foreach ($loggers as $logger) { /* ... */ }
  // ...
}
```

Note:

* `Config::get` will *always* return the value as it was defined originally. No matter, if the
  value is an array, a class instance, a function or a scalar value.
* `Config::child` expected the given path to point to an (associative) array. It wraps this
  array in a `Config` and returns that object.

It is also possible to modify Config objects after initialization. This may be useful if
you want to construct a complete configuration object from different parts of your code,
e.g. allowing different modules to register their configuration or factories.

```php
$c->set('logging/factory', 'AnotherFactoryClass');
```

The above example would modify the `Config` object `$c` in place. I plan to change this
behaviour in version 2 of this library to let `$c` unmodified, and instead return a
new `Config` instance with the updated data.

