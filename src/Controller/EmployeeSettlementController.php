<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\EmployeeSettlementType;
use App\Model\EmployeeSettlement;
use App\Service\EmployeeSettlementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employeeSettlement')]
final class EmployeeSettlementController extends AbstractController
{
    public function __construct(
        private readonly EmployeeSettlementService $employeeSettlementService
    ) {}

    #[Route('/index', name: "app_employeeSettlement_index", methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function index(Request $request): Response
    {
        $currentMonth = intval(date('n'));
        $currentYear = intval(date('Y'));

        $form = $this->createForm(EmployeeSettlementType::class, null, [
            'month' => $currentMonth,
            'year' => $currentYear
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var EmployeeSettlement $employeeSettlement
             */
            $employeeSettlement = $form->getData();
            $employeeSettlement->setMonth($currentMonth);
            $employeeSettlement->setYear($currentYear);

            $file = $this->employeeSettlementService->generateExcel($employeeSettlement);
            return $this->file($file);
        }

        return $this->render('/employeeSettlement/index.html.twig', [
            'form' => $form,
            'currentMonth' => $this->employeeSettlementService->getMonthName($currentMonth),
            'currentYear' => $currentYear
        ]);
    }
}
