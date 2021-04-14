<?php


namespace App\Controller;

use App\Entity\Counter;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\MicroPostType;
use App\Repository\CounterRepository;
use App\Repository\MicroPostRepository;
use App\Repository\UserProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

/**
 * @Route("/micro-post")
 */
class MicroPostController extends AbstractController
{
    /**
     * @var MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        MicroPostRepository $microPostRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        AuthorizationCheckerInterface $authorizationChecker,
        Environment $twig,
        UserRepository $userRepository
    )
    {
        $this->microPostRepository = $microPostRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="micro_post_index")
     * @param TokenStorageInterface $tokenStorage
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(TokenStorageInterface $tokenStorage, UserRepository $userRepository, UserProfileRepository $userProfiles, CounterRepository $counterRepository)
    {
        $profiles = $userProfiles->findAll();
        $currentUser = $tokenStorage->getToken()->getUser();
//        $following = $currentUser->getFollowing();
        $usersToFollow = [];

        if ($currentUser instanceof User){
            $posts = $this->microPostRepository->findAllByUsers($currentUser->getFollowing());

            $usersToFollow = count($posts) === 0 ? $userRepository->findAllWithMoreThan5PostsExeptUser($currentUser) : [];
        } else{
            $posts = $this->microPostRepository->findBy([], ['time' => 'DESC']);
        }
        $views = $this->microPostRepository->getViewsForeachPost();
        $newViews = array_slice($views,0,5,true);

        $likes = $this->microPostRepository->getLikesForeachPost();
        $newLikes = array_slice($likes, 0, 5, true);

        $followers = $this->userRepository->getAllFollowersForeachUser();
        $newFollowers = array_slice($followers, 0, 5, true);

        return $this->render('micro-post/index.html.twig',
        [
//            'following' => $following,
            'newFollowers' => $newFollowers,
            'followers' => $followers,
            'newLikes' =>$newLikes,
            'likes' => $likes,
            'views' => $views,
            'newViews' => $newViews,
            'profiles' => $profiles,
            'posts' => $posts,
            'usersToFollow' => $usersToFollow
        ]);
    }

    /**
     * @Route("/edit/{id}", name="micro_post_edit")
     * @Security("is_granted('edit', microPost)", message="Access denied")
     */
    public function edit(MicroPost $microPost, Request $request )
    {
       // $this->denyAccessUnlessGranted('edit', $micropost);

//        if (!$this->authorizationChecker->isGranted('edit', $microPost)){
//            throw new UnauthorizedHttpException();
//        }
        $form = $this->formFactory->create(
            MicroPostType::class,
            $microPost
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }
        return $this->render('micro-post/add.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/delete/{id}", name="micro_post_delete")
     * @param MicroPost $microPost
     * @return RedirectResponse
     * @Security("is_granted('delete', microPost)", message="Access denied")
     */
    public function delete(MicroPost $microPost)
    {
        $this->entityManager->remove($microPost);
        $this->entityManager->flush();

        $this->flashBag->add('notice', 'Micro post was deleted');

        return new RedirectResponse(
            $this->router->generate('micro_post_index')
        );
    }
    /**
     * @Route("/add", name="micro_post_add")
     * @Security("is_granted('ROLE_USER')")
     */
    public function add(Request $request, TokenStorageInterface $tokenStorage)
    {
        $user = $tokenStorage->getToken()->getUser();
        $microPost = new MicroPost();
   //     $microPost->setTime(new \DateTime());
        $microPost->setUser($user);
        $form = $this->formFactory->create(
            MicroPostType::class,
            $microPost
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->entityManager->persist($microPost);
            $this->entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }
        return $this->render('micro-post/add.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/user/{username}", name="micro_post_user")
     */
    public function userPosts(User $userWithPosts)
    {
        $views = $this->microPostRepository->getViewsForeachPost();
        $likes = $this->microPostRepository->getLikesForeachPost();

        $html = $this->twig->render('micro-post/user-posts.html.twig',
            [
//                'posts' => $this->microPostRepository->findBy(
//                    ['user' => $userWithPosts],
//                    ['time' => 'DESC']
//                )
                'likes' => $likes,
                'views' => $views,
                'posts' => $userWithPosts->getPosts(),
                'user' => $userWithPosts,

            ]);
        return new Response($html);
    }

    /**
     * @Route("/{id}", name="micro_post_post")
     */
    public function post(MicroPost $post, UserProfileRepository $userProfile, CounterRepository $counterRepository)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $db_ip = $counterRepository->findOneBy([
            'ip' => $ip,
            'post' => $post->getId()
        ]);
        if($db_ip === null){
            $counter = new Counter();
            $counter->setPost($post);
            $counter->setDate(new \DateTime());
            $counter->setIp($ip);

            $this->entityManager->persist($counter);
            $this->entityManager->flush();
        }

        $views = $this->microPostRepository->getViewsForeachPost();
        $likes = $this->microPostRepository->getLikesForeachPost();


        return $this->render(
            'micro-post/post.html.twig',
            [
                'likes' => $likes,
                'views' => $views,
                'profile' => $userProfile,
                'post' => $post
            ]
        );
    }
}