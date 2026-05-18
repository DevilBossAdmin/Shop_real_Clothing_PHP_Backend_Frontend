<?php
require_once __DIR__ . '/../backend/bootstrap.php';
function db(): PDO { return Database::connection(); }
