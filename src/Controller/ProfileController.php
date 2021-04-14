<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\ProfileType;
use App\Repository\UserProfileRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;
    /**
     * @var UserProfileRepository
     */
    private $userProfileRepository;
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

    public function __construct(
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        UserProfileRepository $userProfileRepository,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    )
    {
        $this->formFactory = $formFactory;
        $this->userProfileRepository = $userProfileRepository;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    /**
     * @Route("/", name="user_profile")
     * @param TokenStorageInterface $tokenStorage
     * @return Response
     * @return RedirectResponse|Response
     */
    public function index(TokenStorageInterface $tokenStorage): Response
    {
        $currentUser = $tokenStorage->getToken()->getUser();
        if($profile = $this->userProfileRepository->findBy(['information' => $currentUser->getId()])) {
            return $this->render('profile/index.html.twig', [
                'profile' => $profile,
                'user' => $currentUser
            ]);
        }else{
            return new RedirectResponse(
                $this->router->generate('user_profile_add')
            );
        }
    }

    /**
     * @Route("/edit/{id}", name="user_profile_edit")
     * @param Request $request
     * @param UserProfile $userProfile
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, TokenStorageInterface $tokenStorage, User $profile)
    {
        $currentUser = $tokenStorage->getToken()->getUser();
        $userProfile = $profile->getProfile($currentUser);
        $form = $this->formFactory->create(
            ProfileType::class,
            $userProfile
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }
        return $this->render('profile/settings.html.twig',
            [
                'userProfile' => $userProfile,
                'form' => $form->createView()
            ]
        );
    }


    /**
     * @Route("/delete/{id}", name="user_profile_delete")
     * @param UserProfile $userProfile
     * @return RedirectResponse
     */
    public function delete(UserProfile $userProfile)
    {
        $this->entityManager->remove($userProfile);
        $this->entityManager->flush();

        $this->flashBag->add('notice', 'Profile was deleted');

        return new RedirectResponse(
            $this->router->generate('micro_post_index')
        );
    }

    /**
     * @Route("/add", name="user_profile_add")
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @return RedirectResponse|Response
     */
    public function add(Request $request, TokenStorageInterface $tokenStorage, FileUploader $fileUploader)
    {
        $user = $tokenStorage->getToken()->getUser();

            $userProfile = new UserProfile();
            $userProfile->setInformation($user);
            $form = $this->formFactory->create(
                ProfileType::class,
                $userProfile
            );
            $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            /**
             * @var UploadedFile $file
             */
            $file = $request->files->get('profile')['image'];

            if ($file) {
                $filename = $fileUploader->uploadFile($file);
                $userProfile->setImage($filename);
                $this->entityManager->persist($userProfile);
                $this->entityManager->flush();
            }

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }

        return $this->render('profile/settings.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);

    }

}
