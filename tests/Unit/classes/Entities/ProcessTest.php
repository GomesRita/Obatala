<?php declare(strict_types=1);

use Brain\Monkey\Functions;
use Obatala\Entities\Process;
use Obatala\Tests\Unit\TestCase;

class ProcessTest extends TestCase
{
    public function test_get_post_type_returns_correct_slug(): void
    {
        $this->assertSame(
            'process_obatala',
            Process::get_post_type()
        );
    }

    public function test_register_post_type_uses_expected_slug(): void
    {
        Functions\stubs([
            '_x' => fn ($text) => $text,
            '__' => fn ($text) => $text,
        ]);

        Functions\expect('register_post_type')
            ->once()
            ->with('process_obatala', \Mockery::type('array'))
            ->andReturn(true);

        Process::register_post_type();

        $this->assertTrue(true);
    }
}
