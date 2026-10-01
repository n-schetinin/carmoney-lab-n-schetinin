<?php

declare(strict_types=1);

namespace CarMoneyLab\Tests\Unit;

use CarMoneyLab\Domain\ApplicationValidator;
use CarMoneyLab\Domain\AssessmentService;
use CarMoneyLab\Domain\DecisionEngine;
use CarMoneyLab\Domain\LtvCalculator;
use CarMoneyLab\Domain\ValidationException;
use CarMoneyLab\Domain\VehicleAge;
use CarMoneyLab\Domain\VinValidator;
use PHPUnit\Framework\TestCase;

final class AssessmentServiceTest extends TestCase
{
    private AssessmentService $service;

    protected function setUp(): void
    {
        $rules = require __DIR__ . '/../../backend/config/rules.php';
        $age = new VehicleAge((int) date('Y'));

        $this->service = new AssessmentService(
            new ApplicationValidator($rules, new VinValidator($rules['vin']), $age),
            new LtvCalculator(),
            new DecisionEngine($rules['ltv']),
            $age,
            (int) $rules['vehicle']['review_mileage_km'],
        );
    }

    /** @return array<string,mixed> */
    private function payload(int $amount, int $marketValue, int|null $mileage = 96000): array
    {
        $payload = [
            'vin' => 'XTA21099998765432',
            'year' => (int) date('Y') - 4,
            'market_value' => $marketValue,
            'requested_amount' => $amount,
            'term_months' => 24,
        ];

        if ($mileage !== null) {
            $payload['mileage'] = $mileage;
        }

        return $payload;
    }

    public function testApprovesLowLtvAndSetsLimitToRequestedAmount(): void
    {
        $result = $this->service->assess($this->payload(450000, 900000));

        self::assertSame(50.0, $result['ltv']);
        self::assertSame(DecisionEngine::APPROVE, $result['decision']);
        self::assertSame(450000, $result['approved_limit']);
        self::assertSame(4, $result['vehicle_age']);
    }

    public function testSendsMiddleLtvToReviewWithZeroLimit(): void
    {
        $result = $this->service->assess($this->payload(675000, 900000));

        self::assertSame(75.0, $result['ltv']);
        self::assertSame(DecisionEngine::REVIEW, $result['decision']);
        self::assertSame(0, $result['approved_limit']);
    }

    public function testRejectsHighLtv(): void
    {
        $result = $this->service->assess($this->payload(855000, 900000));

        self::assertSame(95.0, $result['ltv']);
        self::assertSame(DecisionEngine::REJECT, $result['decision']);
        self::assertSame(0, $result['approved_limit']);
    }

    public function testApprovesMileageJustBelowReviewThreshold(): void
    {
        $result = $this->service->assess($this->payload(450000, 900000, 399999));

        self::assertSame(50.0, $result['ltv']);
        self::assertSame(DecisionEngine::APPROVE, $result['decision']);
        self::assertSame(450000, $result['approved_limit']);
    }

    public function testApprovesMileageExactlyAtReviewThreshold(): void
    {
        $result = $this->service->assess($this->payload(450000, 900000, 400000));

        self::assertSame(50.0, $result['ltv']);
        self::assertSame(DecisionEngine::APPROVE, $result['decision']);
        self::assertSame(450000, $result['approved_limit']);
    }

    public function testSendsMileageAboveReviewThresholdToReview(): void
    {
        $result = $this->service->assess($this->payload(450000, 900000, 400001));

        self::assertSame(50.0, $result['ltv']);
        self::assertSame(DecisionEngine::REVIEW, $result['decision']);
        self::assertSame(0, $result['approved_limit']);
    }

    public function testRejectsEmptyMileageWithValidationError(): void
    {
        $missingKey = $this->payload(450000, 900000, null);

        $nullValue = $this->payload(450000, 900000);
        $nullValue['mileage'] = null;

        foreach (['missing key' => $missingKey, 'null value' => $nullValue] as $payload) {
            try {
                $this->service->assess($payload);
                self::fail('Ожидали ValidationException');
            } catch (ValidationException $exception) {
                self::assertArrayHasKey('mileage', $exception->errors());
            }
        }
    }
}
