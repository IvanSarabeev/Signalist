<?php

namespace App\Controller\Api;

use App\MessageHandler\EmailNotificationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/stock-transaction', name: 'api_stock_transaction')]
final class StockTransactionController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route(path: '/buy', name: 'buy-stock', methods: 'POST')]
    public function buy(): Response
    {
        $order = new class {
            public function getId(): int
            {
                return 1;
            }

            public function getBuyer(): object
            {
                return new class {
                    public function getEmail(): string
                    {
                        return 'email@xample.tech';
                    }
                };
            }
        };

        $this->bus->dispatch(new EmailNotificationHandler($order));

        return $this->render('stock/example.html.twig');
    }
}
