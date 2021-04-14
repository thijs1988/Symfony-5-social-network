<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\WebLink\Link;

/**
 * @Route("/conversations", name="conversations.")
 */
class ConversationController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, ConversationRepository $conversationRepository, FlashBagInterface $flashBag, RouterInterface $router)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    /**
     * @Route("/{id}", name="newConversations")
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request, UserRepository $userRepository)
    {
        $following = $userRepository->getAllFollowing();

        $otherUser = $request->get('id', 0);
        $otherUser = $this->userRepository->find($otherUser);

        if(is_null($otherUser)) {
            throw new \Exception("The user was not found");

        }

        //cannot create conversation with myself
        if ($otherUser->getId() === $this->getUser()->getId()){
            throw new \Exception("Cannot start a conversation with yourself");

        }

        //Check if conversation already exists
        $conversation = $this->conversationRepository->findConversationByParticipants(
            $otherUser->getId(),
            $this->getUser()->getId()
        );

        if (count($conversation)) {
            $this->flashBag->add('notice', 'Conversation already exists');

            return new RedirectResponse(
                $this->router->generate('index_conversation', [
                    'controller_name' => 'IndexController',
                    'following' => $following
                ])
            );
        }

        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->entityManager->getConnection()->beginTransaction();
        try{
            $this->entityManager->persist($conversation);
            $this->entityManager->persist($participant);
            $this->entityManager->persist($otherParticipant);

            $this->entityManager->flush();
            $this->entityManager->commit();
        }catch(\Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'following' => $following
        ]);

    }

    /**
     * @Route("/", name="getConversations", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getConvs(Request $request){
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());

        $hubUrl = $this->getParameter('mercure.default_hub');

        $this->addLink($request, new Link('mercure', $hubUrl));
        return $this->json($conversations);
    }
}
