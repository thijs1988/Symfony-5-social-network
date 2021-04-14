<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository", repositoryClass=UserRepository::class)
 * @UniqueEntity(fields="email", message="This e-mail is already used")
 * @UniqueEntity(fields="username", message="This username is already used")
 */
class User implements AdvancedUserInterface, \Serializable
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=50)
     */
    private $username;

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=254, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(min=4, max=50)
     * @Assert\Regex(pattern="/\s/", match=true, message="Your name should have a white space.")
     */
    private $fullName;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\MicroPost", mappedBy="likedBy")
     */
    private $postsLiked;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Message", mappedBy="messageBy")
     */
    private $messageNotifications;

    /**
     * @var array
     * @ORM\Column(type="simple_array")
     */
    private $roles;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MicroPost", mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="following")
     */
    private $followers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="followers")
     * @ORM\JoinTable(name="following",
     *     joinColumns={
     *          @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *      },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="following_user_id", referencedColumnName="id")
     *      }
     *     )
     */
    private $following;

    /**
     * @ORM\Column(type="string", nullable=true, length=30)
     */
    private  $confirmationToken;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserPreferences", cascade={"persist"})
     */
    private $preferences;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserProfile", mappedBy="information")
     */
    private $profile;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Participant", mappedBy="user")
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="user")
     */
    private $messages;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->postsLiked = new ArrayCollection();
        $this->roles = [self::ROLE_USER];
        $this->enabled = false;
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getMessageNotifications()
    {
        return $this->messageNotifications;
    }

    /**
     * @param mixed $messageNotifications
     */
    public function setMessageNotifications($messageNotifications): void
    {
        $this->messageNotifications = $messageNotifications;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }



    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {

    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->enabled
        ]);
    }

    public function unserialize($serialized)
    {
       list($this->id, $this->username, $this->password, $this->enabled
           ) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @return ArrayCollection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @return ArrayCollection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    public function follow(User $userToFollow)
    {
        if($this->getFollowing()->contains($userToFollow))
        {
            return;
        }
        $this->getFollowing()->add($userToFollow);
    }

    /**
     * @return ArrayCollection
     */
    public function getPostsLiked()
    {
        return $this->postsLiked;
    }

    /**
     * @return mixed
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param mixed $confirmationToken
     */
    public function setConfirmationToken($confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }


    public function isAccountNonExpired()
    {
        return true;
    }


    public function isAccountNonLocked()
    {
        return true;
    }


    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return UserPreferences|null
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    /**
     * @param mixed $preferences
     */
    public function setPreferences($preferences): void
    {
        $this->preferences = $preferences;
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

//    /**
//     * @return ArrayCollection
//     */
//    public function setProfile($profile): void
//    {
//        $this->profile = $profile;
//    }

    public function addMessageNotification(Message $messageNotifications): self
    {
        if (!$this->messageNotifications->contains($messageNotifications)) {
            $this->messageNotifications[] = $messageNotifications;
            $messageNotifications->addMessageBy($this);
        }

        return $this;
    }

    public function removeMessageNotification(Message $messageNotifications): self
    {
        if ($this->messageNotifications->removeElement($messageNotifications)) {
            $messageNotifications->removeMessageBy($this);
        }

        return $this;
    }

public function addPostsLiked(MicroPost $postsLiked): self
{
    if (!$this->postsLiked->contains($postsLiked)) {
        $this->postsLiked[] = $postsLiked;
        $postsLiked->addLikedBy($this);
    }

    return $this;
}

public function removePostsLiked(MicroPost $postsLiked): self
{
    if ($this->postsLiked->removeElement($postsLiked)) {
        $postsLiked->removeLikedBy($this);
    }

    return $this;
}

public function addPost(MicroPost $post): self
{
    if (!$this->posts->contains($post)) {
        $this->posts[] = $post;
        $post->setUser($this);
    }

    return $this;
}

public function removePost(MicroPost $post): self
{
    if ($this->posts->removeElement($post)) {
        // set the owning side to null (unless already changed)
        if ($post->getUser() === $this) {
            $post->setUser(null);
        }
    }

    return $this;
}

public function addFollower(User $follower): self
{
    if (!$this->followers->contains($follower)) {
        $this->followers[] = $follower;
        $follower->addFollowing($this);
    }

    return $this;
}

public function removeFollower(User $follower): self
{
    if ($this->followers->removeElement($follower)) {
        $follower->removeFollowing($this);
    }

    return $this;
}

public function addFollowing(User $following): self
{
    if (!$this->following->contains($following)) {
        $this->following[] = $following;
    }

    return $this;
}

public function removeFollowing(User $following): self
{
    $this->following->removeElement($following);

    return $this;
}

public function setProfile(?UserProfile $profile): self
{
    // unset the owning side of the relation if necessary
    if ($profile === null && $this->profile !== null) {
        $this->profile->setInformation(null);
    }

    // set the owning side of the relation if necessary
    if ($profile !== null && $profile->getInformation() !== $this) {
        $profile->setInformation($this);
    }

    $this->profile = $profile;

    return $this;
}

/**
 * @return Collection|Participant[]
 */
public function getParticipants(): Collection
{
    return $this->participants;
}

public function addParticipant(Participant $participant): self
{
    if (!$this->participants->contains($participant)) {
        $this->participants[] = $participant;
        $participant->setUser($this);
    }

    return $this;
}

public function removeParticipant(Participant $participant): self
{
    if ($this->participants->removeElement($participant)) {
        // set the owning side to null (unless already changed)
        if ($participant->getUser() === $this) {
            $participant->setUser(null);
        }
    }

    return $this;
}

/**
 * @return Collection|Message[]
 */
public function getMessages(): Collection
{
    return $this->messages;
}

public function addMessage(Message $message): self
{
    if (!$this->messages->contains($message)) {
        $this->messages[] = $message;
        $message->setUser($this);
    }

    return $this;
}

public function removeMessage(Message $message): self
{
    if ($this->messages->removeElement($message)) {
        // set the owning side to null (unless already changed)
        if ($message->getUser() === $this) {
            $message->setUser(null);
        }
    }

    return $this;
}


}
