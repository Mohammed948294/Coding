<?php

declare(strict_types=1);

namespace Dev\Models;

use Core\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }
}
