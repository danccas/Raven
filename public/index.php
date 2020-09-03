<?php

/**
 * Raven - A PHP Framework For Web Artisans
 *
 * @package  Raven
 * @author   Diego Anccas <diego@anccas.org>
 */

define('RAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/core/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/


$app = $kernel->handle(
    $request = Request::capture()
);

$response->routing();