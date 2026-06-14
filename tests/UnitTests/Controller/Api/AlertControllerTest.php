<?php

namespace App\Tests\UnitTests\Controller\Api;

use App\Entity\Alert;
use App\Entity\User;
use App\Presentation\Http\Controller\Api\AlertController;
use App\Presentation\Http\Request\Alert\CreateAlertRequest;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Service\Alert\AlertServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AlertControllerTest extends TestCase
{
    private AlertServiceInterface|MockObject $alertService;
    private AlertController                  $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alertService = $this->createMock(AlertServiceInterface::class);
        $this->controller = new AlertController($this->alertService);
    }

    /**
     * @test
     */
    public function index_returns204_when_user_has_no_alerts(): void
    {
        $user    = $this->createMock(User::class);
        $request = new Request(['page' => 1, 'limit' => 10]);

        $this->alertService
            ->expects($this->once())
            ->method('getAlerts')
            ->willReturn(null);

        $response = $this->controller->index($user, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    public function index_returns200_with_paginated_alerts(): void
    {
        $user    = $this->createMock(User::class);
        $request = new Request(['page' => 1, 'limit' => 10]);

        $alertArray      = ['alert_name' => 'Test Alert', 'alert_type' => 'price'];
        $paginatedResult = $this->createMock(PaginatedResponse::class);
        $paginatedResult->method('toArray')->willReturn([
            'total' => 1, 'page' => 1, 'limit' => 10, 'total_pages' => 1,
        ]);

        $this->alertService
            ->expects($this->once())
            ->method('getAlerts')
            ->willReturn($paginatedResult);

        $response = $this->controller->index($user, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function index_passes_correct_pagination_to_service(): void
    {
        $user    = $this->createMock(User::class);
        $request = new Request(['page' => 2, 'limit' => 5]);

        $this->alertService
            ->expects($this->once())
            ->method('getAlerts')
            ->with(
                $this->equalTo($user),
                $this->callback(fn (PaginatedRequest $p) => $p->page === 2 && $p->limit === 5)
            )
            ->willReturn(null);

        $this->controller->index($user, $request);
    }

    // ── create() ─────────────────────────────────────────────────────────────

    /**
     * @test
     */
    public function create_returns201_with_alert_data(): void
    {
        $user               = $this->createMock(User::class);
        $createAlertRequest = $this->buildCreateAlertRequest();
        $alert              = $this->createMock(Alert::class);

        $alert->method('toArray')->willReturn([
            'alert_name'      => 'Apple at Discount',
            'alert_type'      => 'price',
            'condition_quality' => 'lt',
            'threshold_value' => '150.0000',
            'frequency'       => 'once_per_day',
            'is_active'       => true,
        ]);

        $this->alertService
            ->expects($this->once())
            ->method('createAlert')
            ->with($user, $createAlertRequest)
            ->willReturn($alert);

        $response = $this->controller->create($user, $createAlertRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function create_delegates_to_alert_service(): void
    {
        $user               = $this->createMock(User::class);
        $createAlertRequest = $this->buildCreateAlertRequest();
        $alert              = $this->createMock(Alert::class);
        $alert->method('toArray')->willReturn([]);

        $this->alertService
            ->expects($this->once())
            ->method('createAlert')
            ->with(
                $this->identicalTo($user),
                $this->identicalTo($createAlertRequest)
            )
            ->willReturn($alert);

        $this->controller->create($user, $createAlertRequest);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

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
