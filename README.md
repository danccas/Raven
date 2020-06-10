<p align="center"><img src="https://repository-images.githubusercontent.com/270174093/5cdf3f00-aaf7-11ea-8928-98f8afd5ca52" width="400"></p>


## About Raven

Raven Framework is a High-Performance PHP framework with expressive, elegant syntax. Designed to be fast, easy to use (and learn) and highly scalable.

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:


### Installation

1\. Download the files.
It's recommended that you use git to install Raven.

$ git clone git@github.com:danccas/Raven.git

This will install Raven, requires PHP 7.1 or newer.

2\. Configure your webserver.

For *Apache*, edit your `.htaccess` file with the following:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Note**: If you need to use Raven in a subdirectory add the line `RewriteBase /subdir/` just after `RewriteEngine On`.

For *Nginx*, add the following to your server declaration:

```
server {
    location / {
        try_files $uri $uri/ /index.php;
    }
}
```

### Features
- Web Applications using MVC pattern
- Routing system and basic Middleware support. (PHP-Router)
- Basic Logger
- Request Validation
- Html/Form Builder
- and more...

## Server requirements
- PHP >= 7.1
- OpenSSL PHP Extension
- PDO PHP Extension
- JSON PHP Extension

# Routing

Routing in Raven is done by matching a URL pattern with a callback function.

```php
Route::any('/', function(){
    echo 'hello world!';
});
```

The callback can be any object that is callable. So you can use a regular function:

```php
function hello(){
    echo 'hello world!';
}

Route::any('/', 'hello');
```

Or a class method:

```php
class Awakening {
    public static function hello() {
        echo 'hello world!';
    }
}

Route::any('/', array('Awakening', 'hello'));
```

Or an object method:

```php
class Awakening
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

$Awakening = new Awakening();

Route::any('/', array($Awakening, 'hello')); 
```

Routes are matched in the order they are defined. The first route to match a
request will be invoked.

## Method Routing

By default, route patterns are matched against all request methods. You can respond
to specific methods by placing an identifier before the URL.

```php
Route::get('/', function(){
    echo 'I received a GET request.';
});

Route::post('/', function(){
    echo 'I received a POST request.';
});
```

You can also map multiple methods to a single callback by using a `|` delimiter:

```php
Route::any('/', function(){
    echo 'I received either any request.';
});
```

## Regular Expressions

You can use regular expressions in your routes:

```php
Route::any('/user/:id', array('id' => '[0-9]+'), function(){
    // This will match /user/1234
});
```

## Named Parameters

You can specify named parameters in your routes which will be passed along to
your callback function.

```php
Route::any('/:name/:id', array('name' => '[\w]+', 'id' => '[0-9]+'), function(){
    echo "hello, {$this->route['name']} ({$this->route['id']})!";
});
```


## Optional Parameters

You can specify named parameters that are optional for matching by wrapping
segments in parentheses.

```php
Route::any('/blog(/:year(/:month(/:day)?)?)?', function(){
    // $this->route
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Any optional parameters that are not matched will be passed in as NULL.

## Wildcards

Matching is only done on individual URL segments. If you want to match multiple
segments you can use the `*` wildcard.

```php
Route::path('/blog/', function(){
    // This will match /blog/2000/02/01
});
```

# Overriding

Route allows you to override its default functionality to suit your own needs,
without having to modify any code.

For example, when Route cannot match a URL to a route, it invokes the `notFound`
method which sends a generic `HTTP 404` response. You can override this behavior
by using the `else` method:

```php
Route::else(function(){
    // Display custom 404 page
    Route::view('errors/404.html');
});
```

# Framework Instance

Instead of running Route as a global static class, you can optionally run it
as an object instance.

```php
require 'core/route.php';

$app = Route::g()->init();

$app->any('/', function(){
    echo 'hello world!';
});

```

So instead of calling the static method, you would call the instance method with
the same name on the Engine object.
