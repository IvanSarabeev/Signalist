<?php

namespace App\Tests\UnitTests\Service\Alert;

use App\Entity\Alert;
use App\Entity\Stock;
use App\Entity\User;
use App\Enum\Alert\AlertCondition;
use App\Enum\Alert\AlertFrequency;
use App\Enum\Alert\AlertType;
use App\Presentation\Http\Exception\Services\AlertExistingException;
use App\Presentation\Http\Exception\Services\StockNotFound;
use App\Presentation\Http\Request\Alert\CreateAlertRequest;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Repository\AlertRepository;
use App\Service\Alert\AlertService;
use App\Service\Stock\StockServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AlertServiceTest extends TestCase
{
    private AlertRepository|MockObject        $alertRepository;
    private StockServiceInterface|MockObject  $stockService;
    private EntityManagerInterface|MockObject $entityManager;
    private AlertService                      $alertService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alertRepository = $this->createMock(AlertRepository::class);
        $this->stockService    = $this->createMock(StockServiceInterface::class);
        $this->entityManager   = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->alertService = new AlertService(
            $this->alertRepository,
            $this->stockService,
            $this->entityManager,
            $logger
        );
    }

    /**
     * @test
     */
    public function getAlerts_returns_null_when_user_has_no_alerts(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildPaginatedRequest();

        $this->alertRepository
            ->method('countUserAlerts')
            ->with($user)
            ->willReturn(0);

        $result = $this->alertService->getAlerts($user, $request);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function getAlerts_returns_paginated_response_when_alerts_exist(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildPaginatedRequest(page: 1, limit: 10);
        $alert   = $this->createMock(Alert::class);

        $this->alertRepository->method('countUserAlerts')->willReturn(1);

        $this->alertRepository
            ->method('findUserAlertItems')
            ->with($user, 10, 0)
            ->willReturn([$alert->toArray()]);

        $result = $this->alertService->getAlerts($user, $request);

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertSame(1, $result->total);
        $this->assertSame(1, $result->page);
        $this->assertSame(10, $result->limit);
        $this->assertSame(1, $result->total_pages);
    }

    /**
     * @test
     */
    public function getAlerts_calculates_total_pages_correctly(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildPaginatedRequest(page: 1, limit: 5);

        $this->alertRepository->method('countUserAlerts')->willReturn(11);
        $this->alertRepository->method('findUserAlertItems')->willReturn([]);

        $result = $this->alertService->getAlerts($user, $request);

        // ceil(11 / 5) = 3
        $this->assertSame(3, $result->total_pages);
    }

    /**
     * @test
     */
    public function getAlerts_passes_correct_offset_to_repository(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildPaginatedRequest(page: 3, limit: 10);

        $this->alertRepository->method('countUserAlerts')->willReturn(25);

        // page 3, limit 10 → offset 20
        $this->alertRepository
            ->expects($this->once())
            ->method('findUserAlertItems')
            ->with($user, 10, 20)
            ->willReturn([]);

        $this->alertService->getAlerts($user, $request);
    }

    // ── createAlert() ─────────────────────────────────────────────────────────

    /**
     * @test
     */
    public function createAlert_throws_when_alert_already_exists(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildCreateAlertRequest();

        $this->alertRepository
            ->method('findOneBy')
            ->willReturn($this->createMock(Alert::class));

        $this->expectException(AlertExistingException::class);

        $this->alertService->createAlert($user, $request);
    }

    /**
     * @test
     */
    public function createAlert_throws_when_stock_not_found(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildCreateAlertRequest();

        $this->alertRepository->method('findOneBy')->willReturn(null);

        $this->stockService
            ->method('findStockBySymbol')
            ->with('AAPL')
            ->willReturn(null);

        $this->expectException(StockNotFound::class);

        $this->alertService->createAlert($user, $request);
    }

    /**
     * @test
     */
    public function createAlert_persists_and_returns_alert(): void
    {
        $user    = $this->createMock(User::class);
        $stock   = $this->createMock(Stock::class);
        $request = $this->buildCreateAlertRequest();

        $this->alertRepository->method('findOneBy')->willReturn(null);
        $this->stockService->method('findStockBySymbol')->willReturn($stock);

        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Alert::class));
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->alertService->createAlert($user, $request);

        $this->assertInstanceOf(Alert::class, $result);
    }

    /**
     * @test
     */
    public function createAlert_maps_request_fields_onto_alert_correctly(): void
    {
        $user    = $this->createMock(User::class);
        $stock   = $this->createMock(Stock::class);
        $request = $this->buildCreateAlertRequest();

        $this->alertRepository->method('findOneBy')->willReturn(null);
        $this->stockService->method('findStockBySymbol')->willReturn($stock);
        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        $result = $this->alertService->createAlert($user, $request);

        $this->assertSame('Apple at Discount', $result->getAlertName());
        $this->assertSame(AlertType::PRICE, $result->getAlertType());
        $this->assertSame(AlertCondition::LESS_THAN, $result->getConditionQuality());
        $this->assertSame(AlertFrequency::ONCE_PER_DAY, $result->getFrequency());
        $this->assertSame('150', $result->getThresholdValue());
        $this->assertTrue($result->isActive());
        $this->assertNotNull($result->getCreatedAt());
    }

    /**
     * @test
     */
    public function createAlert_does_not_persist_when_stock_not_found(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildCreateAlertRequest();

        $this->alertRepository->method('findOneBy')->willReturn(null);
        $this->stockService->method('findStockBySymbol')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(StockNotFound::class);
        $this->alertService->createAlert($user, $request);
    }

    /**
     * @test
     */
    public function createAlert_does_not_persist_when_alert_already_exists(): void
    {
        $user    = $this->createMock(User::class);
        $request = $this->buildCreateAlertRequest();

        $this->alertRepository
            ->method('findOneBy')
            ->willReturn($this->createMock(Alert::class));

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(AlertExistingException::class);
        $this->alertService->createAlert($user, $request);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildPaginatedRequest(int $page = 1, int $limit = 10): PaginatedRequest
    {
        return new PaginatedRequest(page: $page, limit: $limit);
    }

    private function buildCreateAlertRequest(): CreateAlertRequest
    {
        return new CreateAlertRequest(
            symbol:           'AAPL',
            alertName:        'Apple at Discount',
            alertType:        'price',
            conditionQuality: 'lt',
            frequency:        'once_per_day',
            thresholdValue:   '150',
        );
    }

}
