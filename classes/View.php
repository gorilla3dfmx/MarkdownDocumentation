<?php
/**
 * Simple View class for rendering templates
 */
class View {
    public static function render($template, $data = []) {
        extract($data);

        ob_start();
        include TEMPLATES_PATH . '/' . $template . '.php';
        $content = ob_get_clean();

        return $content;
    }

    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
