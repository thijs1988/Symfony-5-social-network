<?php


namespace App\Event;


use App\Entity\UserPreferences;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;


    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                [
                    'onKernelRequest',
                    20
                ]
            ]
        ];
    }

//    public function onRequest()
//    {
//        return [KernelEvents::EXCEPTION => [
//            ['processException', 10],
//            ['logException', 0],
//            ['notifyException', -10],
//        ],
//        ];
//    }

    public function onKernelRequest(RequestEvent $event): void
    {

        $request = $event->getRequest();
        if (!$request->hasPreviousSession()){
            return;
        }

        if ($locale = $request->attributes->get('_locale')){
            $request->getSession()->set('_locale', $locale);
        } else {
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }
}