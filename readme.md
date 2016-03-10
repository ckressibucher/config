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

I was tired of using too many `isset` throghout all my code. The main
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

// ... but with a `Config` it looks nicer:
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

You can ask for a value which is nested in a child array by providing a *path* of keys, separated
by a Slash `/`:

```php
$loggingFactoryClass = $c->get('logging/factory');
```

If any of the used path parts (keys) don't exist, the default value (`null` if
not explicitly specified) is returned.

Say you want to use all of the *logging* data, e.g. to instantiate your logging instance.
For this, you can get a child config object:

```php
$loggingConfig = $c->child('logging'); /* @var $loggingConfig Config */

// we can now provide only the relevant data to thr logging factory
$myLoggingFactory = new $loggingFactoryClass;
$logger = $myLoggingFactory->create($loggingConfig);

// the logging factory "sees" only the data of the "logging" child array
public function create(Config $cfg)
{
  $loggers = $cfg->get('loggers'); // $loggers is an array
  foreach ($loggers as $logger) { /* ... */ }
  // ...
}
```

To summarize:

* `Config::get` will *always* return the value as it was defined originally. No matter, if the
  value is an array, a class instance, a function or a scalar value.
* `Config::child` expects the given path to point to an (associative) array. It wraps this
  array in a `Config` and returns that object.

A new value can be set using `Config::set`:

```php
$newConfig = $c->set('logging/factory', 'AnotherFactoryClass');
```

This method returns *a new instance* with a specific part of the config updated (or newly set).
This may be useful if you want to construct a complete configuration object
in multiple steps from different parts of your code,
e.g. to allow different modules to register their configuration or factories.
In the above example, `$newConfig` has a value of `'AnotherFactoryClass'` at the path
*logging/factory*, where the `$c` instance is *not* modified and still has the old value.
(Note that this behaviour is new in Version 2.0, former versions did update the data in place).

