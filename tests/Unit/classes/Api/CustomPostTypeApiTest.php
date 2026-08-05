<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

class CustomPostTypeApiTest extends TestCase{
    public function test_get_post_type_returns_correct_slug(): void
    {
        $this->assertSame('process_obatala', \Obatala\Entities\Process::get_post_type());
    }
}