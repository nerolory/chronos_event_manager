<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Глобальная настройка для всех тестов.
     * Мы переопределяем метод setUp, чтобы гарантированно отключать Vite
     * во всех дочерних Feature-тестах без поиска несуществующих трейтов.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (method_exists($this, 'withoutVite')) {
            $this->withoutVite();
        }
    }
}