<?php

namespace App\Message;

readonly class EmailNotification
{
    public function __construct(
        private object $order,
    ) {
    }

    public function getOrder(): object
    {
        return $this->order;
    }
}
