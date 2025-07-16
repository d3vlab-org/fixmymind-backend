<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Get all routes
$routes = $app->make('router')->getRoutes();

echo "All API routes:\n";

foreach ($routes as $route) {
    $uri = $route->uri();
    $methods = implode('|', $route->methods());

    if (strpos($uri, 'api/') === 0 || strpos($uri, 'me') !== false) {
        echo "Route: {$methods} {$uri}\n";
        echo "Action: " . $route->getActionName() . "\n";
        echo "Middleware: " . implode(', ', $route->middleware()) . "\n";
        echo "---\n";
    }
}
