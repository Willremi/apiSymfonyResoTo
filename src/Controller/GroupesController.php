<?php

namespace App\Controller;

use App\Entity\Groupes;
use App\Repository\GroupesRepository;
use App\Repository\RegionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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
        Groupes $groupes,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonGroupe = $serializer->serialize($groupes, 'json', ['groups' => 'getGroupes']);
        return new JsonResponse($jsonGroupe, Response::HTTP_OK, [], true);
    }

    #[Route('/api/groupe', name: 'app_add_groupe', methods: ['POST'])]
    public function createGroupe(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, RegionsRepository $regionsRepository): JsonResponse
    {
        $groupe = $serializer->deserialize($request->getContent(), Groupes::class, 'json');

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idRegion. S'il n'est pas défini, alors on met -1 par défaut
        $idRegion = $content['idRegion'] ?? -1;

        // On cherche la région qui correspond et on l'assigne au groupe.
        // Si "find" ne trouve pas la région, alors null sera retourné.
        $groupe->setRegions($regionsRepository->find($idRegion));

        $em->persist($groupe);
        $em->flush();

        $jsonGroupe = $serializer->serialize($groupe, 'json', ['groups' => 'getGroupes']);

        $location = $urlGenerator->generate('app_detail_groupe', ['id' => $groupe->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonGroupe, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/groupe/{id}', name: 'app_update_groupe', methods: ['PUT'])]
    public function updateGroupe(Request $request, SerializerInterface $serializer, Groupes $currentGroupes, EntityManagerInterface $em, RegionsRepository $regionsRepository): JsonResponse
    {
        $updatedGroupe = $serializer->deserialize(
            $request->getContent(),
            Groupes::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentGroupes]
        );
        $content = $request->toArray();
        $idRegion = $content['idRegion'] ?? -1;
        $updatedGroupe->setRegions($regionsRepository->find($idRegion));

        $em->persist($updatedGroupe);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/groupe/{id}', name: 'app_delete_groupe', methods: ['DELETE'])]
    public function deleteGroupe(Groupes $groupes, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($groupes);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
