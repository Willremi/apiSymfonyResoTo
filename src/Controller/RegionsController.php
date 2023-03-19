<?php

namespace App\Controller;

use App\Entity\Regions;
use App\Repository\RegionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RegionsController extends AbstractController
{
    #[Route('/api/regions', name: 'app_regions', methods: ['GET'])]
    public function getRegionsList(RegionsRepository $regionsRepository, SerializerInterface $serializer): JsonResponse
    {
        $regionsList = $regionsRepository->findAll();
        $jsonRegionsList = $serializer->serialize($regionsList, 'json', ['groups' => 'getRegions']);
        return new JsonResponse($jsonRegionsList, Response::HTTP_OK, [], true);
    }
    
    #[Route('/api/region/{id}', name: 'app_detail_regions', methods: ['GET'])]
    public function getDetailRegion(Regions $regions, SerializerInterface $serializer): JsonResponse
    {
        $jsonRegion = $serializer->serialize($regions, 'json', ['groups'=> 'getRegions']);
        return new JsonResponse($jsonRegion, Response::HTTP_OK, [], true);
    }
}
