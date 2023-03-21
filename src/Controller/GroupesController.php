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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupesController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des groupes
     *
     * @param GroupesRepository $groupesRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
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

    /**
     * Cette méthode permet de récupérer un groupe particulier en fonction de son id
     *
     * @param Groupes $groupes
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/groupe/{id}', name: 'app_detail_groupe', methods: ['GET'])]
    public function getDetailGroupe(
        Groupes $groupes,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonGroupe = $serializer->serialize($groupes, 'json', ['groups' => 'getGroupes']);
        return new JsonResponse($jsonGroupe, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de créer un groupe
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param RegionsRepository $regionsRepository
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/groupe', name: 'app_add_groupe', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants pour créer un groupe")]
    public function createGroupe(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, RegionsRepository $regionsRepository, ValidatorInterface $validator): JsonResponse
    {
        $groupe = $serializer->deserialize($request->getContent(), Groupes::class, 'json');

        //Vérification des erreurs
        $errors = $validator->validate($groupe);

        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($groupe);
        $em->flush();

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idRegion. S'il n'est pas défini, alors on met -1 par défaut
        $idRegion = $content['idRegion'] ?? -1;

        // On cherche la région qui correspond et on l'assigne au groupe.
        // Si "find" ne trouve pas la région, alors null sera retourné.
        $groupe->setRegions($regionsRepository->find($idRegion));


        $jsonGroupe = $serializer->serialize($groupe, 'json', ['groups' => 'getGroupes']);

        $location = $urlGenerator->generate('app_detail_groupe', ['id' => $groupe->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonGroupe, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Cette méthode permet de mettre à jour un groupe
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Groupes $currentGroupes
     * @param EntityManagerInterface $em
     * @param RegionsRepository $regionsRepository
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/groupe/{id}', name: 'app_update_groupe', methods: ['PUT'])]
    public function updateGroupe(Request $request, SerializerInterface $serializer, Groupes $currentGroupes, EntityManagerInterface $em, RegionsRepository $regionsRepository, ValidatorInterface $validator): JsonResponse
    {
        $updatedGroupe = $serializer->deserialize(
            $request->getContent(),
            Groupes::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentGroupes]
        );

        //Vérification des erreurs
        $errors = $validator->validate($updatedGroupe);

        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idRegion = $content['idRegion'] ?? -1;
        $updatedGroupe->setRegions($regionsRepository->find($idRegion));

        $em->persist($updatedGroupe);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de supprimer un groupe
     *
     * @param Groupes $groupes
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/groupe/{id}', name: 'app_delete_groupe', methods: ['DELETE'])]
    public function deleteGroupe(Groupes $groupes, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($groupes);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
