<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tes memakai database MySQL khusus (peta_asn_test), bukan SQLite in-memory:
     * query Haversine di SpkController memakai fungsi MySQL (RADIANS, ACOS)
     * yang tidak ada di SQLite. Kredensial diambil dari .env seperti biasa;
     * hanya nama databasenya yang dialihkan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite(); // View HTTP tests do not need a compiled frontend manifest.
    }

    public function createApplication(): Application
    {
        $app = parent::createApplication();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'peta_asn_test',
        ]);

        return $app;
    }
}
