#!/usr/bin/env php
<?php

// Script para corrigir links hardcoded em todas as views

$basePath = __DIR__;
$viewsPath = $basePath . '/app/Views';

$fixes = [
    // Juries
    'juries/planning.php' => [
        ['/juries/vacancy/', 'url(\'/juries/vacancy/'],
        ['/juries/planning-by-vacancy"', 'url(\'/juries/planning-by-vacancy\')"'],
        ['/juries/planning"', 'url(\'/juries/planning\')"'],
    ],
    'juries/manage_vacancy.php' => [
        ['/juries/planning-by-vacancy"', 'url(\'/juries/planning-by-vacancy\')"'],
        ['/juries/planning?', 'url(\'/juries/planning?'],
        ['/juries/planning"', 'url(\'/juries/planning\')"'],
    ],
    'juries/show.php' => [
        ['/juries/', 'url(\'/juries/'],
    ],
    'juries/index_print.php' => [
        ['/juries/export/excel"', 'url(\'/juries/export/excel\')"'],
    ],
    // Locations
    'locations/import.php' => [
        ['/locations/export/template"', 'url(\'/locations/export/template\')"'],
    ],
    'locations/dashboard.php' => [
        ['/locations"', 'url(\'/locations\')"'],
    ],
    'locations/templates.php' => [
        ['action="/locations/templates/', 'action="<?= url(\'/locations/templates/'],
        ['action="/locations/templates"', 'action="<?= url(\'/locations/templates\') ?>"'],
    ],
    // Applications
    'applications/dashboard.php' => [
        ['/applications/export"', 'url(\'/applications/export\')"'],
        ['/applications"', 'url(\'/applications\')"'],
    ],
    'applications/history.php' => [
        ['/applications?', 'url(\'/applications?'],
    ],
    'applications/index.php' => [
        ['action="/applications"', 'action="<?= url(\'/applications\') ?>"'],
    ],
    // Availability
    'availability/index.php' => [
        ['/availability/', 'url(\'/availability/'],
    ],
    'availability/request_change.php' => [
        ['/availability"', 'url(\'/availability\')"'],
        ['action="/availability/change/submit"', 'action="<?= url(\'/availability/change/submit\') ?>"'],
    ],
    'availability/request_cancel.php' => [
        ['/availability"', 'url(\'/availability\')"'],
        ['action="/availability/', 'action="<?= url(\'/availability/'],
    ],
    'availability/index_old.php' => [
        ['action="/availability"', 'action="<?= url(\'/availability\') ?>"'],
    ],
    // Auth
    'auth/register.php' => [
        ['/login"', 'url(\'/login\')"'],
    ],
    'auth/forgot.php' => [
        ['href="/login"', 'href="<?= url(\'/login\') ?>"'],
    ],
    // Dashboard
    'dashboard/index.php' => [
        ['/availability"', 'url(\'/availability\')"'],
    ],
    // Guides
    'guides/show.php' => [
        ['/guides/', 'url(\'/guides/'],
    ],
    // Home
    'home/index.php' => [
        ['/guides/', 'url(\'/guides/'],
        ['/register"', 'url(\'/register\')"'],
        ['/login"', 'url(\'/login\')"'],
    ],
    // Install
    'install/master_data.php' => [
        ['/master-data/', 'url(\'/master-data/'],
        ['/juries/planning"', 'url(\'/juries/planning\')"'],
    ],
    // Juries planning_modals
    'juries/planning_modals.php' => [
        ['action="/juries"', 'action="<?= url(\'/juries\') ?>"'],
        ['action="/juries/create-location-batch"', 'action="<?= url(\'/juries/create-location-batch\') ?>"'],
    ],
    'juries/index_old.php' => [
        ['action="/juries/', 'action="<?= url(\'/juries/'],
        ['action="/juries"', 'action="<?= url(\'/juries\') ?>"'],
    ],
];

echo "Corrigindo links hardcoded...\n\n";

foreach ($fixes as $file => $replacements) {
    $filePath = $viewsPath . '/' . $file;

    if (!file_exists($filePath)) {
        echo "❌ Arquivo não encontrado: $file\n";
        continue;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;

    foreach ($replacements as [$search, $replace]) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "✅ Corrigido: $file\n";
    } else {
        echo "⚠️  Nenhuma alteração: $file\n";
    }
}

echo "\n✅ Concluído!\n";
