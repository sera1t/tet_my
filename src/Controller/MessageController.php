<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/messages', name: 'message.')]
class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALIZE = ['id', 'content', 'createdAt', 'mine'];

    private EntityManagerInterface $entityManager;
    private MessageRepository $messageRepository;

    public function __construct(EntityManagerInterface $entityManager, MessageRepository $messageRepository)
    {

        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
    }

    #[Route('/{id}', name: 'getMessages')]
    public function index(Request $request, Conversation $conversation): Response
    {

        $this->denyAccessUnlessGranted('view', $conversation);

        $messages = $this->messageRepository->findMessageByConversationId($conversation->getId());

        /**
         * @var $message Message
         */

        array_map(function ($message){
            $message->setMine(
                $message->getUser()->getId() === $this->getUser()->getId() ? true: false
            );
        }, $messages);



        return $this->json($message, Response::HTTP_OK, [], [
            'attributes' => self::ATTRIBUTES_TO_SERIALIZE
        ]);
    }
}
