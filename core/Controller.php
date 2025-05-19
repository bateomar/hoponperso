<?php

class Controller
{
    protected function render($viewPath, $pageName = null)
    {
        if ($pageName !== null) {
            $GLOBALS['pageName'] = $pageName; // pour que header.php sache quel CSS charger
        }

        require_once __DIR__ . '/../views/' . $viewPath . '/index.php';
    }
}
