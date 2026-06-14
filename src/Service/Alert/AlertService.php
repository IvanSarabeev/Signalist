<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\Alert;
use App\Entity\User;
use App\Presentation\Http\Exception\Services\AlertExistingException;
use App\Presentation\Http\Exception\Services\StockNotFound;
use App\Presentation\Http\Request\Alert\CreateAlertRequest;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Repository\AlertRepository;
use App\Service\Stock\StockServiceInterface;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Psr\Log\LoggerInterface;

final readonly class AlertService implements AlertServiceInterface
{
    private const ALERT_PREFIX = 'Alert: ';

    public function __construct(
        private AlertRepository        $alertRepository,
        private StockServiceInterface  $stockService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger,
    )
    { }

    /**
     * Get all the user alerts
     *
     * @param User $user
     * @param PaginatedRequest $paginatedRequest
     * @return PaginatedResponse|null
     */
    public function getAlerts(User $user, PaginatedRequest $paginatedRequest): ?PaginatedResponse
    {
        $total = $this->alertRepository->countUserAlerts($user);

        if ($total === 0) {
            return null;
        }

        $alerts = $this->alertRepository->findUserAlertItems(
            $user,
            $paginatedRequest->limit,
            $paginatedRequest->getOffset()
        );

        return new PaginatedResponse(
            items:       $alerts,
            total:       $total,
            page:        $paginatedRequest->page,
            limit:       $paginatedRequest->limit,
            total_pages: (int) ceil($total / $paginatedRequest->limit)
        );
    }

    /**
     * Create Alert for a specific user
     *
     * @param User $user
     * @param CreateAlertRequest $createAlertRequest
     * @return Alert
     *
     * @throws AlertExistingException - When an Alert is already existing
     * @throws StockNotFound - When a Stock is missing
     * @throws Exception
     */
    public function createAlert(User $user, CreateAlertRequest $createAlertRequest): Alert
    {
        $findAlert = $this->alertRepository->findOneBy(['user' => $user, 'stock' => $createAlertRequest->symbol]);

        if ($findAlert !== null) {
            throw new AlertExistingException();
        }

        $stock = $this->stockService->findStockBySymbol($createAlertRequest->symbol);

        if ($stock === null) {
            throw new StockNotFound();
        }

        $createdAt = new DateTimeImmutable('now', new DateTimeZone('Europe/Sofia'));

        $alert = new Alert();
        $alert->setUser($user);
        $alert->setStock($stock);
        $alert->setAlertName($createAlertRequest->alertName);
        $alert->setAlertType($createAlertRequest->getAlertType());
        $alert->setConditionQuality($createAlertRequest->getAlertCondition());
        $alert->setFrequency($createAlertRequest->getAlertFrequency());
        $alert->setThresholdValue($createAlertRequest->thresholdValue);
        $alert->setCreatedAt($createdAt);

        $this->entityManager->persist($alert);

        try {
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $this->logger->error(self::ALERT_PREFIX . 'Entity Manager error', [
                'message' => $exception->getMessage(),
            ]);

            throw new AlertExistingException();
        }

        return $alert;
    }
}
