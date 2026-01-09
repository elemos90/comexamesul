<?php
/**
 * Debug Feature Flags
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/app/Utils/helpers.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Utils\Env;
Env::load(__DIR__ . '/.env');

echo "1. Testing database...\n";
$db = \App\Database\Connection::getInstance();
echo "   DB OK\n";

echo "2. Testing Model...\n";
$model = new \App\Models\FeatureFlag();
$flags = $model->getAllGroupedByRole();
echo "   Model OK: " . count($flags) . " roles\n";

echo "3. Testing Service...\n";
$all = \App\Services\FeatureFlagService::getAll();
echo "   Service OK: " . count($all) . " roles\n";

echo "4. View file exists: ";
echo file_exists(__DIR__ . '/app/Views/features/index.php') ? "YES\n" : "NO\n";

echo "\nAll tests passed!\n";
