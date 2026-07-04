<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\Alert;
use App\Entity\User;
use App\Presentation\Http\Exception\Services\Alert\AlertDeletionFailed;
use App\Presentation\Http\Exception\Services\Alert\AlertExistingException;
use App\Presentation\Http\Exception\Services\Alert\AlertNotFoundException;
use App\Presentation\Http\Exception\Services\Alert\AlertNothingToUpdateException;
use App\Presentation\Http\Exception\Services\Alert\AlertUpdateException;
use App\Presentation\Http\Exception\Services\StockNotFound;
use App\Presentation\Http\Request\Alert\CreateAlertRequest;
use App\Presentation\Http\Request\Alert\UpdateAlertRequest;
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
use Throwable;

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
     * Get a specific user Alert
     *
     * @param User $user
     * @param int $id
     * @return array
     *
     * @throws AlertNotFoundException - When the Alert is missing
     */
    public function getAlert(User $user, int $id): array
    {
        $alert = $this->alertRepository->findUserAlertItem($user, $id);

        if (!$alert) {
            throw new AlertNotFoundException();
        }

        return $alert->toAlert();
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
            $this->entityManager->rollback();

            $this->logger->error(self::ALERT_PREFIX . 'Entity Manager error', [
                'message' => $exception->getMessage(),
            ]);

            throw new AlertExistingException();
        }

        return $alert;
    }

    /**
     * Update partially an Alert
     *
     * @param User $user
     * @param int $id
     * @param UpdateAlertRequest $updateAlertRequest
     * @return Alert
     *
     * @throws AlertNothingToUpdateException - When there's nothing to update.
     * @throws StockNotFound - When the stock is missing for the specific user.
     * @throws AlertNotFoundException - When the alert is missing.
     * @throws AlertUpdateException - When the alert entity failed to update.
     */
    public function updateAlert(User $user, int $id, UpdateAlertRequest $updateAlertRequest): Alert
    {
        if ($updateAlertRequest->isEmpty()) {
            throw new AlertNothingToUpdateException();
        }

        $findAlert = $this->alertRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (!$findAlert) {
            throw new AlertNotFoundException();
        }

        $stock = $this->stockService->findStockBySymbol($findAlert->getStock()->getSymbol());

        if ($stock === null) {
            throw new StockNotFound();
        }

        $alert = $this->alertRepository->findUserAlertWithStock($user, $stock);

        if ($alert === null) {
            throw new AlertNotFoundException();
        }

        if ($updateAlertRequest->alertName !== null) {
            $alert->setAlertName($updateAlertRequest->alertName);
        }

        if ($updateAlertRequest->alertType !== null) {
            $alert->setAlertType($updateAlertRequest->getAlertType());
        }

        if ($updateAlertRequest->conditionQuality !== null) {
            $alert->setConditionQuality($updateAlertRequest->getAlertConditionQuality());
        }

        if ($updateAlertRequest->frequency !== null) {
            $alert->setFrequency($updateAlertRequest->getAlertFrequency());
        }

        if ($updateAlertRequest->thresholdValue !== null) {
            $alert->setThresholdValue($updateAlertRequest->thresholdValue);
        }

        if ($updateAlertRequest->isActive !== null) {
            $alert->setIsActive($updateAlertRequest->isActive);
        }

        try {
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $this->logger->error(self::ALERT_PREFIX . 'Failed to update alert', [
                'message' => $exception->getMessage(),
            ]);

            throw new AlertUpdateException();
        }

        return $alert;
    }

    /**
     * Delete a specific alert for a user
     *
     * @param User $user
     * @param int $id
     * @return void
     *
     * @throws AlertNotFoundException - When the Alert is missing
     */
    public function deleteAlert(User $user, int $id): void
    {
        $alert = $this->alertRepository->findUserAlertItem($user, $id);

        if (!$alert) {
            throw new AlertNotFoundException();
        }

        try {
            $this->entityManager->remove($alert);
            $this->entityManager->flush();
        } catch (Throwable) {
            throw new AlertDeletionFailed();
        }
    }
}
