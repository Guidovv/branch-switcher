# BranchSwitcher
BranchSwitcher is a package that lets you switch Git branches via the browser
<br>
![BranchSwitcher Screenshot](https://i.ibb.co/mbxXnJf/Schermafdruk-van-2020-11-13-14-40-43.png)

### Requires
---
**php:** `^7.3`<br>
**laravel/framework:** `^6.18`

<br>

### Installation
---
Require this package with composer
```
composer require guidovv/branch-switcher
```

<br>

### Usage
---
##### Enabling / disabling
By default this package is enabled on the **`local`** and **`testing`** environment.<br>
This can be overwritten by publishing the config and changing it to fit your needs.

##### Toggling 
By default the branch switcher is hidden on the page.<br>
You can toggle it by pressing **`ctrl` + `b`**.

<br>

### Default commands
---
This package comes with a few default commands that are shown to the user.<br>
By publishing the config you can control what commands are being shown to the user and which are checked by default.
```php
'commands' => [
    'npm install' => [
        'default' => 1,
    ],
    'composer install' => [
        'default' => 1,
    ],
    'php artisan migrate' => [
        'default' => 1,
    ],
    'php artisan migrate:fresh' => [
        'default' => 1,
    ],
    'php artisan db:seed' => [
        'default' => 0,
    ],
]
```
<br>

### Publishing
---
To publish the config run:
```
php artisan vendor:publish --tag=branch-switcher-config
```

To publish the resources (JS/CSS) run:
```
php artisan vendor:publish --tag=branch-switcher-resources
```

<br>

### Dependencies
---
None

<br>

### License
---
MIT
