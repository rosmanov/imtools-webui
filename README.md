# ImTools Web UI

ImTools Web UI provides a Web interface demonstrating features of the [ImTools](https://bitbucket.org/osmanov/imtools) project.

# Configuration

Default configuration files are stored in `conf` directory. To override them create `conf/local.php` file:
```php
<?php
return [
    'config-name-1' => [
        'option-1' => 'option-1-value',
        ...
    ],
    ...
];
```
where the top-level keys should match basenames of the files in the `conf` directory.

For instance, `conf/xxx.php` file with the following contents:
```php
<?php
return [
    'Abc' => '123'
];
```
can be overridden with the following `conf/local.php` file:
```php
<?php
return [
    'xxx' => [
        'Abc' => '123'
    ]
];
```

## Web server (Nginx) configuration

See [`conf/examples/nginx/imtools-webui.conf`](conf/examples/nginx/imtools-webui.conf)

## Database schema

See [`dbschema.sql`](dbschema.sql)
