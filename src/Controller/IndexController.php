<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchType;
use App\Repository\UserRepository;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class IndexController extends AbstractController
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {

        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/index_conversation", name="index_conversation")
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(TokenStorageInterface $tokenStorage, Request $request, FormFactoryInterface $formFactory, UserRepository $userRepository)
    {
        $following = $userRepository->getAllFollowing();
        $username = $this->getUser()->getUsername();

        $token = (new Builder())
            ->withClaim('mercure', ['subscribe' => [sprintf("/index_conversation/%s", $username)]])
            ->getToken(
                new Sha256(),
                new Key($this->getParameter('mercure_secret_key'))
            );

        $response = $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
//            'form' => $form->createView()
                'following' => $following
        ]);

        $response->headers->setCookie(
            new Cookie(
                'mercureAuthorisation',
                $token,
                (new \DateTime())
                ->add(new \DateInterval('PT2H')),
                '/.well-known/mercure',
                null,
                false,
                true,
                false,
                'strict'
            )
        );

        return $response;
    }

    /**
     * @Route("/handleSearch", name="handleSearch")
     * @param Request $request
     * @return JsonResponse
     */
    public function handleSearch(Request $request, userRepository $userRepository, SerializerInterface $serializer)
    {
        $following = "";
        $query = $request->request->get('name');
        if($query){
            $following = $this->userRepository->getAllFollowingForeachUser($query);
        }

        if($following){
            $encoders = [
                new JsonEncoder(),
            ];
            $normalizers = [
                new ObjectNormalizer(),
            ];
            $serializer = new Serializer($normalizers, $encoders);

            $data = $serializer->serialize($following, 'json');
            return new JsonResponse($data, 200, [], true);
        }
        return $this->json($following);
    }

    /**
     * @Route("/search", name="search")
     * @return JsonResponse
     */
    public function search(Request $request){
       $name = $request->request->get('name');

       return $this->json($name);
    }
}
