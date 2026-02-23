<?php

$excludeList = ['.git', 'node_modules', 'tests', 'writable', 'public/test_wizard.php', 'release.zip', 'scripts'];
$rootFiles = ['.env.production', 'bootstrap.php', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'README.md', 'DEPLOY.md', 'database_production.sql'];
$rootDirs = ['app', 'config', 'resources', 'routes', 'storage', 'vendor'];
$publicDirs = ['assets', 'css', 'img', 'js', 'uploads']; // Move these to root

$zipName = 'release_v1.0.zip';

if (file_exists($zipName)) {
    unlink($zipName);
}

$zip = new ZipArchive();
if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("Cannot open <$zipName>\n");
}

echo "Adding root files...\n";
foreach ($rootFiles as $file) {
    if (file_exists($file)) {
        if ($file === '.env.production') {
            $zip->addFile($file, '.env');
        } else {
            $zip->addFile($file, $file);
        }
    }
}

echo "Adding root directories...\n";
foreach ($rootDirs as $dir) {
    if (!is_dir($dir))
        continue;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        $path = $file->getPathname();
        $relativePath = str_replace('\\', '/', $path);
        // Exclude logs
        if (strpos($relativePath, 'storage/logs/') !== false && $file->isFile() && $file->getExtension() === 'log')
            continue;
        if ($file->isDir()) {
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($path, $relativePath);
        }
    }
}

echo "Flating public/ files to root...\n";

// 1. Move public/.htaccess to root/.htaccess
if (file_exists('public/.htaccess')) {
    $zip->addFile('public/.htaccess', '.htaccess');
    echo "Moved public/.htaccess to root\n";
}

// 2. Move public/index.php to root/index.php and fix bootstrap path
if (file_exists('public/index.php')) {
    $content = file_get_contents('public/index.php');
    // Change require_once __DIR__ . '/../bootstrap.php'; to require_once __DIR__ . '/bootstrap.php';
    $content = str_replace("'/../bootstrap.php'", "'/bootstrap.php'", $content);
    $zip->addFromString('index.php', $content);
    echo "Moved and patched public/index.php\n";
}

// 3. Move public folders (css, js, etc) to root
foreach ($publicDirs as $dir) {
    $source = 'public/' . $dir;
    if (!is_dir($source))
        continue;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        $path = $file->getPathname();
        // Remove 'public/' prefix from relative path
        $relativePath = substr(str_replace('\\', '/', $path), 7);

        if ($file->isDir()) {
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($path, $relativePath);
        }
    }
}

// 4. Overwrite app/Utils/helpers.php with patched public_path() for flattened structure
if (file_exists('app/Utils/helpers.php')) {
    $content = file_get_contents('app/Utils/helpers.php');
    // Change base_path('public' . ...) to base_path('' . ...)
    $content = str_replace("base_path('public' .", "base_path('' .", $content);
    $zip->addFromString('app/Utils/helpers.php', $content);
    echo "Patched app/Utils/helpers.php public_path()\n";
}

$zip->close();
echo "Successfully created FLATTENED $zipName\n";
