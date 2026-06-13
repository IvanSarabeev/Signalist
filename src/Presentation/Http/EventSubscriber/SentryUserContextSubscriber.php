<?php

namespace App\Presentation\Http\EventSubscriber;

use Sentry\State\Scope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use function Sentry\configureScope;

class SentryUserContextSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        configureScope(function (Scope $scope) use ($user): void {
            $scope->setUser([
                'id'   => $user->getUserIdentifier(),
                'role' => $user->getRoles(),
            ]);
        });
    }
}
