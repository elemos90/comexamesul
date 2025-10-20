<?php

namespace App\Controllers;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        extract($data);
        ob_start();
        include view_path($view . '.php');
        $content = ob_get_clean();

        if ($layout === null) {
            return $content;
        }

        ob_start();
        include view_path($layout . '.php');
        return ob_get_clean();
    }
}
