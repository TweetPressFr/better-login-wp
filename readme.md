# Better Login WP #

A VERY simple plugin to enforce security with logins & passwords.

## How to install

1. Install and activate this plugin
2. Enjoy

## Requirements

* PHP 5.4

## Filters

* blw_weak_passwd_list

example : 

```php
add_filter( 'blw_weak_passwd_list', 'add_weak_passwords_to_main_list' );
function add_weak_passwords_to_main_list( $list ) {
	$list[] = 'test';
	return $list;
} 
```
this snippet would add "test" as weak password reference.

## Changelog

### 2017

* initial