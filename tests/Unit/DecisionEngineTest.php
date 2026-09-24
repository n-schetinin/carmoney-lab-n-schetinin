<?php

declare(strict_types=1);

namespace CarMoneyLab\Tests\Unit;

use CarMoneyLab\Domain\DecisionEngine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DecisionEngineTest extends TestCase
{
    private DecisionEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new DecisionEngine((include __DIR__ . '/../../backend/config/rules.php')['ltv']);
    }

    #[DataProvider('ltvValues')]
    public function testDecidesByLtv(float $ltv, string $expected): void
    {
        self::assertSame($expected, $this->engine->decide($ltv));
    }

    /** @return array<string,array{float,string}> */
    public static function ltvValues(): array
    {
        return [
            'низкий LTV' => [28.5, DecisionEngine::APPROVE],
            'середина зелёной зоны' => [45.0, DecisionEngine::APPROVE],
            'серая зона' => [72.3, DecisionEngine::REVIEW],
            'верхняя граница зелёной зоны' => [62.0, DecisionEngine::APPROVE],
            'верхняя граница серой зоны' => [85.0, DecisionEngine::REVIEW],
            'сразу за верхней границей' => [85.01, DecisionEngine::REJECT],
            'высокий LTV' => [120.0, DecisionEngine::REJECT],
        ];
    }
}
