<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Obatala\Entities\Process;
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

class ProcessTest extends TestCase
{
    public function test_get_post_type_returns_correct_slug(): void
    {
        $this->assertSame(
            'process_obatala',
            Process::get_post_type()
        );
    }

    public function test_process_slug(){
        $postType = get_post_type_object('process_obatala');

        $this->assertEquals(
            'obatala_processes',
            $postType->rewrite['slug']
        );
    }

}