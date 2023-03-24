<?php

namespace App\Controller;

use App\Entity\Regions;
use App\Repository\RegionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class RegionsController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des régions.
     *
     * @param RegionsRepository $regionsRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/regions', name: 'app_regions', methods: ['GET'])]
    public function getRegionsList(RegionsRepository $regionsRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        // $regionsList = $regionsRepository->findAll();
        // $jsonRegionsList = $serializer->serialize($regionsList, 'json', ['groups' => 'getRegions']);
        // return new JsonResponse($jsonRegionsList, Response::HTTP_OK, [], true);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 1);

        $idCache = "getRegionsList-" . $page . "-" . $limit;

        $jsonRegionsList = $cache->get($idCache, function (ItemInterface $item) use ($regionsRepository, $page, $limit, $serializer) {
            $item->tag("groupesCache");
            $regionsList = $regionsRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(['getRegions']);
            return $serializer->serialize($regionsList, 'json', $context);
        });

        return new JsonResponse($jsonRegionsList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer une région en particulier en fonction de son id.
     *
     * @param Regions $regions
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/region/{id}', name: 'app_detail_region', methods: ['GET'])]
    public function getDetailRegion(Regions $regions, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getRegions']);
        $jsonRegion = $serializer->serialize($regions, 'json', $context);
        return new JsonResponse($jsonRegion, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de créer une nouvelle région.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/region/add', name: 'app_add_region', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants pour créer une région")]
    public function createRegion(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
        $region = $serializer->deserialize($request->getContent(), Regions::class, 'json');

        //Vérification des erreurs
        $errors = $validator->validate($region);

        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($region);
        $em->flush();

        // Vider le cache
        $cache->invalidateTags(['groupesCache']);

        $context = SerializationContext::create()->setGroups(['getRegions']);

        $jsonRegion = $serializer->serialize($region, 'json', $context);
        $location = $urlGenerator->generate('app_detail_region', ['id'=> $region->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonRegion, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Cette méthode permet de mettre à jour une région
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Regions $currentRegions
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/region/edit/{id}', name: 'app_update_region', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants pour éditer une région")]
    public function updateRegion(Request $request, SerializerInterface $serializer, Regions $currentRegions, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
        $newRegions = $serializer->deserialize($request->getContent(), Regions::class, 'json');

        $currentRegions->setName($newRegions->getName());

        //Vérification des erreurs
        $errors = $validator->validate($currentRegions);

        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($currentRegions);
        $em->flush();

        // Vide le cache
        $cache->invalidateTags(['groupesCache']);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de supprimer une région
     *
     * @param Regions $regions
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/region/delete/{id}', name: 'app_delete_region', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants pour supprimer une région")]
    public function deleteGroupe(Regions $regions, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        $em->remove($regions);
        $em->flush();

        // Vide le cache
        $cache->invalidateTags(['groupesCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
