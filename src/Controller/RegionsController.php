<?php

namespace App\Controller;

use App\Entity\Regions;
use App\Repository\RegionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegionsController extends AbstractController
{
    #[Route('/api/regions', name: 'app_regions', methods: ['GET'])]
    public function getRegionsList(RegionsRepository $regionsRepository, SerializerInterface $serializer): JsonResponse
    {
        $regionsList = $regionsRepository->findAll();
        $jsonRegionsList = $serializer->serialize($regionsList, 'json', ['groups' => 'getRegions']);
        return new JsonResponse($jsonRegionsList, Response::HTTP_OK, [], true);
    }
    
    #[Route('/api/region/{id}', name: 'app_detail_region', methods: ['GET'])]
    public function getDetailRegion(Regions $regions, SerializerInterface $serializer): JsonResponse
    {
        $jsonRegion = $serializer->serialize($regions, 'json', ['groups'=> 'getRegions']);
        return new JsonResponse($jsonRegion, Response::HTTP_OK, [], true);
    }

    #[Route('/api/region', name: 'app_add_region', methods: ['POST'])]
    public function createRegion(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $region = $serializer->deserialize($request->getContent(), Regions::class, 'json');

        //Vérification des erreurs
        $errors = $validator->validate($region);

        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($region);
        $em->flush();

        $jsonRegion = $serializer->serialize($region, 'json', ['groups' => 'getRegions']);
        $location = $urlGenerator->generate('app_detail_region', ['id'=> $region->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonRegion, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/region/{id}', name: 'app_update_region', methods: ['PUT'])]
    public function updateRegion(Request $request, SerializerInterface $serializer, Regions $currentRegions, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $updatedRegion = $serializer->deserialize($request->getContent(), Regions::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentRegions]);

        //Vérification des erreurs
        $errors = $validator->validate($updatedRegion);

        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($updatedRegion);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/region/{id}', name: 'app_delete_region', methods: ['DELETE'])]
    public function deleteGroupe(Regions $regions, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($regions);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
