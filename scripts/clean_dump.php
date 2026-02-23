<?php

$file = __DIR__ . '/../database_production.sql';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$content = file_get_contents($file);

// Remove DEFINER=`root`@`localhost`
// Pattern handles differents quotings commonly used by mysqldump
$pattern = '/\sDEFINER=`[^`]+`@`[^`]+`/i';
$replacement = '';

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent === null) {
    die("Error processing file.\n");
}

file_put_contents($file, $newContent);

echo "Successfully removed DEFINER clauses from database_production.sql\n";
