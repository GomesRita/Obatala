<?php declare(strict_types=1);

use Brain\Monkey\Functions;
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

    public function test_register_routes_registers_expected_routes(): void
    {
        $registeredRoutes = [];

        Functions\stubs([
            'register_rest_route' => function (string $namespace, string $route, array $args) use (&$registeredRoutes) {
                $registeredRoutes[] = compact('namespace', 'route', 'args');
            },
        ]);

        $api = new class extends ProcessTypeApi {
            public function callRegisterRoutes(): void
            {
                $this->register_routes();
            }
        };

        $api->callRegisterRoutes();

        $this->assertCount(6, $registeredRoutes);
        $this->assertSame('obatala/v1', $registeredRoutes[0]['namespace']);

        $routeNames = array_column($registeredRoutes, 'route');
        $this->assertContains('process_type/(?P<id>\d+)/meta', $routeNames);
        $this->assertContains('process_type/(?P<id>\d+)/assosiate_sector', $routeNames);
        $this->assertContains('process_type/(?P<id>\d+)/get_node', $routeNames);
        $this->assertContains('process_type/upload', $routeNames);
        $this->assertContains('process_type/download', $routeNames);
    }

    public function test_get_post_type_returns_correct_slug(): void
    {
        $this->assertSame('process_obatala', \Obatala\Entities\Process::get_post_type());
    }

}
