<?php

$file = __DIR__ . '/.env';

if (!file_exists($file)) {
    die("Error: .env file not found at $file\n");
}

$content = file_get_contents($file);
$bom = "\xEF\xBB\xBF";

// Check for BOM
if (substr($content, 0, 3) === $bom) {
    echo "BOM detected! Removing...\n";
    $content = substr($content, 3);
    file_put_contents($file, $content);
    echo "BOM removed successfully.\n";
} else {
    echo "No BOM found at the start of the file.\n";
}

// Double check
$content = file_get_contents($file);
if (substr($content, 0, 3) === $bom) {
    echo "FAILED: BOM still present.\n";
} else {
    echo "VERIFIED: File is clean (no BOM).\n";
}

// Print first 50 chars as hex to be sure
echo "First 10 chars hex: " . bin2hex(substr($content, 0, 10)) . "\n";
