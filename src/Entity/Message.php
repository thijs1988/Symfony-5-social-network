<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;


/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @ORM\Table(indexes={@Index(name="created_at_index", columns={"created_at"})})
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    use Timestamp;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="messages")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conversation", inversedBy="messages")
     */
    private $conversation;

    private $mine;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="messageNotifications")
     * @ORM\JoinTable(name="message_notifications",
     *     joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *     )
     */
    private $messageBy;

    public function __construct()
    {
        $this->messageBy = new ArrayCollection();
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



    /**
     * @return mixed
     */
    public function getMine()
    {
        return $this->mine;
    }

    /**
     * @param mixed $mine
     */
    public function setMine($mine): void
    {
        $this->mine = $mine;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function addMessageBy(User $messageBy): self
    {
        if (!$this->messageBy->contains($messageBy)) {
            $this->messageBy[] = $messageBy;
        }

        return $this;
    }
    public function removeMessageBy(User $messageBy): self
    {
        $this->messageBy->removeElement($messageBy);

        return $this;
    }

    public function sendNotification(User $user)
    {
        if ($this->messageBy->contains($user)){
            return;
        }
        $this->messageBy->add($user);
    }
}
