# BCA-PHP-CURL

Work with remote servers via cURL much easier than using the native PHP bindings.

## Requirements

1. PHP 5.3+
2. libcurl

## Features

* POST/GET/PUT/DELETE requests over HTTP
* HTTP Authentication
* Follows redirects
* Returns error string
* Provides debug information
* Cookies

## Download

https://github.com/brodkinca/BCA-PHP-CURL

## Examples

Simple requests can be constructed with just a URL and a method.
```php
$data = new CURL('http://example.com/')->get();
```
More complex requests build upon that concept by adding methods to the request.
```php
$request = new CURL('http://example.com/');

$response = $request
	->param('aaa', 'bbb')
	->param('xxx', 'yyy')
	->post();

echo $response;
```
Advanced requests can be built by adding even more methods.
```php
$request = new CURL('http://example.com/');

$response = $request
    ->param('aaa', 'bbb')
    ->param('xxx', 'yyy')
    ->option(CURLOPT_PROXY, '10.0.0.1')
    ->auth('username', 'password', 'digest')
    ->delete();

echo $response;
```
