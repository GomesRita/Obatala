<?php

namespace Obatala\Tests\Unit;

use Brain\Monkey;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Toda classe de teste UNITÁRIO deve estender esta classe.
 * Ela liga/desliga o Brain Monkey (que intercepta as funções do WP)
 * a cada teste, garantindo isolamento entre eles.
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}