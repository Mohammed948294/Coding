<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DocumentModel;
use Core\Auth;

final class SearchController extends BaseController
{
    public function index(): void
    {
        $filters = $_GET;
        $results = [];
        if (!empty($_GET)) {
            $results = (new DocumentModel())->search((int) Auth::appUser()['tenant_id'], $filters);
        }
        $this->render('search/index', compact('results', 'filters'));
    }
}
