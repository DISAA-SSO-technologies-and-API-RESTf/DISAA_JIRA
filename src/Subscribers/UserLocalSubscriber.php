<?php

namespace App\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UserLocalSubscriber implements EventSubscriberInterface {
	private $session;
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * UserLocalSubscriber constructor.
	 */
	public function __construct( RequestStack $requestStack) {
		$this->requestStack = $requestStack;

	}

	public function onLogin(InteractiveLoginEvent $event) {
		$user = $event->getAuthenticationToken()->getUser();
		if(!is_null($user->getLocale())){
			//$this->session->set('_locale', $user->getLocale());
			$this->requestStack->getSession()->set('_locale', $user->getLocale());
		}
	}

	public static function getSubscribedEvents() {
		return [
			SecurityEvents::INTERACTIVE_LOGIN => [
				['onLogin', 15]
			]
		];
	}
}