<?php declare(strict_types=1);

use Obatala\Api\ProcessTypeApi;
use Obatala\Tests\Unit\TestCase;

class ProcessTypeTest extends TestCase
{
    public function test_get_meta_args_contains_step_order_and_flowData(): void
    {
        $api = new class extends ProcessTypeApi {
            public function callGetMetaArgs()
            {
                return $this->get_meta_args();
            }
        };

        $meta_args = $api->callGetMetaArgs();

        $this->assertArrayHasKey('step_order', $meta_args);
        $this->assertArrayHasKey('flowData', $meta_args);
    }
}
