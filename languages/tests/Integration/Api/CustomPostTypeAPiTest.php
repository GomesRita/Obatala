<?php

namespace Obatala\Tests\Integration\Api;

use Obatala\Api\CustomPostTypeApi;
use WP_REST_Request;
use WP_UnitTestCase;

class CustomPostTypeApiTest extends WP_UnitTestCase
{
    public function test_get_collection_returns_registered_processes(): void
    {
        // Cria posts reais no banco de teste
        $post_id = self::factory()->post->create([
            'post_type'  => 'process_obatala',
            'post_title' => 'Processo de teste',
        ]);

        // Registra as rotas de verdade
        $api = new CustomPostTypeApi();
        $api->register_routes();
        do_action('rest_api_init'); // dispara o hook que registra tudo

        // Simula uma requisição REST real
        $request = new WP_REST_Request('GET', '/obatala/v1/process_obatala');
        $response = rest_do_request($request);

        $this->assertSame(200, $response->get_status());
        $this->assertNotEmpty($response->get_data());
    }
}