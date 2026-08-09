<?php declare(strict_types=1);

use Obatala\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Obatala\Api\SectorApi;

class SectorApiTest extends TestCase
{
    public function test_register_routes_registers_expected_routes(): void
    {
        $registeredRoutes = [];

        Functions\stubs([
            'register_rest_route' => function (string $namespace, string $route, array $args) use (&$registeredRoutes) {
                $registeredRoutes[] = compact('namespace', 'route', 'args');
            },
        ]);

        $api = new class extends SectorApi {
            public function callRegisterRoutes(): void
            {
                $this->register_routes();
            }
        };

        $api->callRegisterRoutes();

        $this->assertCount(10, $registeredRoutes);
        $this->assertSame('obatala/v1', $registeredRoutes[0]['namespace']);

        $routeNames = array_column($registeredRoutes, 'route');
        $this->assertContains('create_sector_obatala', $routeNames);
        $this->assertContains('get_sector_obatala/(?P<sector_id>[a-zA-Z0-9_\-.]+)', $routeNames);
        $this->assertContains('all_sector_obatala', $routeNames);
        $this->assertContains('update_sector_obatala/(?P<sector_id>[a-zA-Z0-9_\-.]+)', $routeNames);
        $this->assertContains('delete_sector_obatala/(?P<sector_id>[a-zA-Z0-9_\-.]+)', $routeNames);
        $this->assertContains('sector_obatala/users_obatala', $routeNames);
        $this->assertContains('associate_user_to_sector', $routeNames);
        $this->assertContains('sector_obatala/(?P<sector_id>[a-zA-Z0-9_\-.]+)/users', $routeNames);
        $this->assertContains('sector_obatala/sectors_with_users', $routeNames);
        $this->assertContains('sector_obatala/(?P<sector_id>[a-zA-Z0-9_\-.]+)/remove_user', $routeNames);
    }

    public function test_create_sector_route_has_required_args(): void
    {
        $registeredRoutes = [];

        Functions\stubs([
            'register_rest_route' => function (string $namespace, string $route, array $args) use (&$registeredRoutes) {
                $registeredRoutes[] = compact('namespace', 'route', 'args');
            },
        ]);

        $api = new class extends SectorApi {
            public function callRegisterRoutes(): void
            {
                $this->register_routes();
            }
        };

        $api->callRegisterRoutes();

        $createRoute = array_values(array_filter($registeredRoutes, static function (array $route) {
            return $route['route'] === 'create_sector_obatala';
        }));

        $this->assertCount(1, $createRoute);

        $args = $createRoute[0]['args'];
        $this->assertSame('POST', $args['methods']);
        $this->assertSame(['Obatala\Entities\Sector', 'add_sector'], $args['callback']);
        $this->assertArrayHasKey('sector_name', $args['args']);
        $this->assertArrayHasKey('sector_description', $args['args']);
        $this->assertArrayHasKey('sector_status', $args['args']);
        $this->assertTrue($args['args']['sector_name']['validate_callback']('name'));
        $this->assertFalse($args['args']['sector_name']['validate_callback'](''));
        $this->assertTrue($args['args']['sector_description']['validate_callback']('desc'));
        $this->assertFalse($args['args']['sector_description']['validate_callback'](''));
        $this->assertTrue($args['args']['sector_status']['validate_callback']('active'));
        $this->assertFalse($args['args']['sector_status']['validate_callback'](''));
    }

    public function test_associate_user_to_sector_validation_callbacks(): void
    {
        $registeredRoutes = [];

        Functions\stubs([
            'register_rest_route' => function (string $namespace, string $route, array $args) use (&$registeredRoutes) {
                $registeredRoutes[] = compact('namespace', 'route', 'args');
            },
        ]);

        $api = new class extends SectorApi {
            public function callRegisterRoutes(): void
            {
                $this->register_routes();
            }
        };

        $api->callRegisterRoutes();

        $associateRoute = array_values(array_filter($registeredRoutes, static function (array $route) {
            return $route['route'] === 'associate_user_to_sector';
        }));

        $this->assertCount(1, $associateRoute);

        $args = $associateRoute[0]['args'];
        $this->assertSame('POST', $args['methods']);
        $this->assertSame(['Obatala\Entities\Sector', 'associate_user_to_sector'], $args['callback']);

        $userIdValidator = $args['args']['user_id']['validate_callback'];
        $sectorIdValidator = $args['args']['sector_id']['validate_callback'];

        $this->assertTrue($userIdValidator(5));
        $this->assertFalse($userIdValidator('abc'));
        $this->assertFalse($userIdValidator(-1));

        $this->assertTrue($sectorIdValidator('1'));
        $this->assertFalse($sectorIdValidator(1));
        $this->assertFalse($sectorIdValidator('0'));
    }
}
