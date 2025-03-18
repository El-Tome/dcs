<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(
        ApplicationRepository $applicationRepository
    ): Response
    {
        $applis = $applicationRepository->getApplicationByGrandClient();

        $groupedApplis = [];

        foreach ($applis as $appli) {
            $grandClient = $appli['grandClient'];

            if (!isset($groupedApplis[$grandClient])) {
                $groupedApplis[$grandClient] = [];
            }

            $groupedApplis[$grandClient][] = [
                'application' => $appli['application'],
                'totalPrix' => $appli['totalPrix']
            ];
        }

        return $this->render(
            'appli.html.twig',
            [
                'groupedApplis' => $groupedApplis,
            ]
        );
    }

#[Route('/grand-client', name: 'app_grand_client')]
    public function grandClient(
        ApplicationRepository $applicationRepository
    ): Response
    {
        $grandClients = $applicationRepository->getGrandClient(5);

        return $this->render(
            'grandClients.html.twig',
            [
                'grandClients' => $grandClients,
            ]
        );
    }
}
