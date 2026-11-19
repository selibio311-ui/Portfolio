<?php
/*
 * Database connection (PDO + prepared statements everywhere).
 * Real error details go to the server log, never to the visitor.
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            // DB_DSN is only used by automated tests; production uses MySQL.
            if (defined('DB_DSN')) {
                $pdo = new PDO(DB_DSN, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } else {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,   // real prepared statements
                ]);
            }
        } catch (PDOException $ex) {
            error_log('Database connection failed: ' . $ex->getMessage());
            http_response_code(500);
            exit('The website is temporarily unavailable. Please try again later.');
        }
    }

    return $pdo;
}
