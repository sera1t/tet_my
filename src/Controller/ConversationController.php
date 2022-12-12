<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/conversation', name: 'conversations.')]

class ConversationController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private ConversationRepository $conversationRepository;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, ConversationRepository $conversationRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @return JsonResponse
     * @throws Exception
     * @param  Request $request
     */
    #[Route('/', name: 'newConversation', methods: 'POST')]
    public function index(Request $request): JsonResponse
    {
        $otherUser = $request->get('otherUser', 0);
        $otherUser = $this->userRepository->find($otherUser);

        if(is_null($otherUser)) {
            throw new \Exception('The user was not found.');
        }

        if($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception('That`s deep but you cannot create a conversation with yourself');
        }

        $conversation = $this->conversationRepository->findConversationByParticipants(
            $otherUser->getId(),
            $this->getUser()->getId()
        );

        if(count($conversation)) {
          throw new Exception('The conversation already exists');
        };

        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($conversation);
            $this->entityManager->persist($participant);
            $this->entityManager->persist($otherParticipant);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $this->json([
            'id' => $conversation->getId(),
        ], Response::HTTP_CREATED, [], []);
    }

    #[Route('/', name: 'getConversation', methods: 'GET')]
    public function getConvs()
    {
        $conversation = $this->conversationRepository->findConversationByUser($this->getUser()->getId());
        return $this->json($conversation);
    }

}
