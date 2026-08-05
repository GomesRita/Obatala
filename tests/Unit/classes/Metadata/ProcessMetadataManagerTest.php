<?php

namespace Obatala\Tests\Unit\Metadata;

use Brain\Monkey\Functions;
use Obatala\Metadata\ProcessMetadataManager;
use Obatala\Tests\Unit\TestCase;

class ProcessMetadataManagerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidStepIdProvider')]
    public function test_save_metadata_returns_false_for_invalid_step_id($invalid_step_id): void
    {
        Functions\stubs(['error_log' => true]);

        $result = ProcessMetadataManager::save_metadata($invalid_step_id, 'algum valor');

        $this->assertFalse($result);
    }

    public static function invalidStepIdProvider(): array
    {
        return [
            'string não numérica' => ['abc'],
            'zero'                => [0],
            'negativo'            => [-5],
        ];
    }

    public function test_save_metadata_persists_when_data_is_valid(): void
    {
        Functions\stubs([
            'sanitize_text_field' => fn ($value) => trim($value),
            'wp_json_encode'      => fn ($value) => json_encode($value),
        ]);

        Functions\expect('update_post_meta')
            ->once()
            ->with(42, \Mockery::type('string'), \Mockery::type('string'))
            ->andReturn(true);

        $result = ProcessMetadataManager::save_metadata(
            42,
            ['name' => 'Campo X', 'value' => 'valor']
        );

        $this->assertTrue($result);
    }

    public function test_save_metadata_fails_and_logs_when_wp_update_fails(): void
    {
        Functions\stubs([
            'sanitize_text_field' => fn ($value) => $value,
            'wp_json_encode'      => fn ($value) => json_encode($value),
        ]);

        Functions\expect('update_post_meta')->once()->andReturn(false);
        Functions\expect('error_log')->once();

        $result = ProcessMetadataManager::save_metadata(
            42,
            ['name' => 'Campo X', 'value' => 'valor']
        );

        $this->assertFalse($result);
    }
}
