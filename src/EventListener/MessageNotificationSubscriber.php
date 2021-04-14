<?php


namespace App\EventListener;


use App\Entity\Message;
use App\Entity\MessageNotification;

use App\Repository\ParticipantRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;

class MessageNotificationSubscriber implements EventSubscriber
{

    /**
     * @var ParticipantRepository
     */
    private $participantRepository;

    public function __construct(ParticipantRepository $participantRepository)
    {

        $this->participantRepository = $participantRepository;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var PersistentCollection $collectionUpdate */
        foreach ($uow->getScheduledCollectionUpdates() as $collectionUpdate){
            if(!$collectionUpdate->getOwner() instanceof Message){
                continue;
            }

            if ('messageBy' !== $collectionUpdate->getMapping()['fieldName']){
                continue;
            }

            $insertDiff = $collectionUpdate->getInsertDiff();
            if (!count($insertDiff)){
                return;
            }

            /** @var Message $message */
            $message = $collectionUpdate->getOwner();

            $recipient = $this->participantRepository->findParticipantByConversationIdAndUserId(
                $message->getConversation()->getId(),
                $message->getUser()->getId()
            );
            $notification = new MessageNotification();
            $notification->setUser($message->getUser());
            $notification->setMessage($message);
            $notification->setMessageBy($recipient->getUser());

            $em->persist($notification);

            $uow->computeChangeSet(
                $em->getClassMetadata(MessageNotification::class),
                $notification
            );

        }
    }
}