<?php

namespace App\Controller;

use App\Entity\Groupes;
use App\Repository\GroupesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupesController extends AbstractController
{
    #[Route('/api/groupes', name: 'app_groupes', methods: ['GET'])]
    public function getGroupesList(GroupesRepository $groupesRepository, SerializerInterface $serializer): JsonResponse
    {
        // return $this->json([
        //     'message' => 'Welcome to your new controller!',
        //     'path' => 'src/Controller/GroupesController.php',
        // ]);
            $groupesList = $groupesRepository->findAll();
            $jsonGroupesList = $serializer->serialize($groupesList, 'json', ['groups' => 'getGroupes']);

            return new JsonResponse($jsonGroupesList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/groupe/{id}', name: 'app_detail_groupe', methods: ['GET'])]
    public function getDetailGroupe(
        Groupes $groupes, SerializerInterface $serializer
        ): JsonResponse
    {
        $jsonGroupe = $serializer->serialize($groupes, 'json', ['groups' => 'getGroupes']);
        return new JsonResponse($jsonGroupe, Response::HTTP_OK, [], true);
    }
}
