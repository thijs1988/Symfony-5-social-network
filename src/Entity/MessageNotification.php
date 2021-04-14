<?php

namespace App\Entity;

use App\Repository\MessageNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageNotificationRepository::class)
 */
class MessageNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Message")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $messageBy;

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessageBy()
    {
        return $this->messageBy;
    }

    /**
     * @param mixed $messageBy
     */
    public function setMessageBy($messageBy): void
    {
        $this->messageBy = $messageBy;
    }




}
