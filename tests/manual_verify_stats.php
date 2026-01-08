<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Mock helpers if needed
if (!function_exists('view')) {
    function view($path, $data = [])
    {
        echo "View loaded: $path\n";
        print_r(array_keys($data));
        return "View content";
    }
}
if (!function_exists('view_path')) {
    function view_path($path)
    {
        return __DIR__ . '/../app/Views/' . $path;
    }
}
if (!function_exists('url')) {
    function url($path)
    {
        return "http://localhost$path";
    }
}
if (!function_exists('base_path')) {
    function base_path($path)
    {
        return __DIR__ . '/../' . $path;
    }
}
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $default;
    }
}

// Mock Auth
class MockAuth
{
    public static function user()
    {
        return ['id' => 1, 'role' => 'admin', 'name' => 'Admin User'];
    }
    public static function id()
    {
        return 1;
    }
    public static function check()
    {
        return true;
    }
}
class_alias('MockAuth', 'App\Utils\Auth');

// Mock Controller base class if it has dependencies we can't easily satisfy
// But let's try to use the real one if possible, or mock it if it's abstract or complex.
// The real Controller likely uses the 'view' method we mocked above if it's a helper, 
// or $this->view if it's a method.
// Let's check App\Controllers\Controller.

use App\Controllers\StatsController;

try {
    echo "Instantiating StatsController...\n";
    $controller = new StatsController();

    echo "Calling index()...\n";
    // We need to mock the view method in the controller if it exists there.
    // If Controller extends something that has 'view', we might need to override it or ensure our helper is used.

    $output = $controller->index();
    echo "\nSuccess! Output received.\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
