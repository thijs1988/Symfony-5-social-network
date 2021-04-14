<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\UserProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ToFollowController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * ToFollowController constructor.
     * @param EntityManagerInterface $entityManager
     * @param RouterInterface $router
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @Route("/to-follow")
     */
    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @Route("/{id}", name="users_to_follow")
     * @param TokenStorageInterface $tokenStorage
     * @return mixed
     */
    public function index(TokenStorageInterface $tokenStorage, UserRepository $userRepository, UserProfileRepository $userProfile)
    {
        $profile = $userProfile->findAll();
        $currentUser = $tokenStorage->getToken()->getUser();

        $utf = $userRepository->getAllUsersToFollow($currentUser->getFollowing());

        $usersToFollow = $userRepository->getAllUsersToFollow($currentUser->getFollowing());
        if (($key = array_search($currentUser, $usersToFollow)) !== false) {
            unset($usersToFollow[$key]);
        }

        return $this->render('to-follow/to-follow.html.twig', [
            'utf' => $utf,
            'profile' => $profile,
            'usersToFollow' => $usersToFollow
        ]);
    }
}