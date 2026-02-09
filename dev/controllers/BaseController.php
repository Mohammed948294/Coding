<?php

declare(strict_types=1);

namespace Dev\Controllers;

use Core\Controller;

abstract class BaseController extends Controller
{
    protected function viewPath(string $view): string
    {
        return __DIR__ . '/../views/' . $view . '.php';
    }
}
