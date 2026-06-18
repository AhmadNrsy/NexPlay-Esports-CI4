<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        try {

            $db = \Config\Database::connect();

            $query = $db->query("SELECT DATABASE() as db");

            return json_encode($query->getRow());

        } catch (\Throwable $e) {

            return $e->getMessage();

        }
    }
}
