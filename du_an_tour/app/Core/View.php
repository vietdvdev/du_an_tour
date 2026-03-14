<?php
namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): string
    {
        $viewPath = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = VIEW_PATH . '/' . str_replace('.', '/', $layout) . '.php';
        if (!file_exists($viewPath)) {
            return '<p>View not found: ' . htmlspecialchars($view) . '</p>';
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            ob_start();
            include $layoutPath;
            return ob_get_clean();
        }
        return $content;
    }
}
