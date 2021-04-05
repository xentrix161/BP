<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Services\InputValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    private $inputValidationService;
    public function __construct(InputValidationService $inputValidationService)
    {
        $this->inputValidationService = $inputValidationService;
    }

    /**
     * Vyrendruje zoznam všetkých objednávok.
     * @Route("/admin/", name="order_index", methods={"GET"})
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    //TODO: odstranit
    /**
     * Vyrendruje formulár na vytvorenie novej objednávky.
     * @Route("/admin/new", name="order_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newInvoiceNumber = $this->createInvoiceNumber();

            $entityManager = $this->getDoctrine()->getManager();
            $order->setDate(new \DateTime());
            $order->setInvoiceNumber($newInvoiceNumber);
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('order_index');
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Zobrazí objednávku podľa ID.
     * @Route("/admin/{id}", name="order_show", methods={"GET"})
     * @param Order $order
     * @return Response
     */
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * Vyrendruje formulár na edit objednávky podľa ID.
     * @Route("/admin/{id}/edit", name="order_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Order $order
     * @return Response
     */
    public function edit(Request $request, Order $order): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $price = $formData->getTotalPrice();
            $mobile = $formData->getMobile();

            $bool = $this->inputValidationService
                ->price($price)
                ->mobileNumber($mobile)
                ->validate();

            $message = $this->inputValidationService->getMessage();
            if (!empty($message)) {
                $this->addFlash('info', $message);
            }

            if ($bool) {
                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('order_index');
            }
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Vymaže objednávku poďla ID.
     * @Route("/admin/{id}", name="order_delete", methods={"DELETE"})
     * @param Request $request
     * @param Order $order
     * @return Response
     */
    public function delete(Request $request, Order $order): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('order_index');
    }

    /**
     * Vytvorí unikátne číslo faktúry.
     * @return int
     */
    private function createInvoiceNumber()
    {
        $currDate = date("ym");

        $lastInvoiceThisMonth = $this->getDoctrine()->getRepository(Order::class)
            ->findBy(array(),array('invoice_number'=>'DESC'),1,0);
        $invoiceNum = (int)$lastInvoiceThisMonth[0]->getInvoiceNumber();

        if (!is_null($invoiceNum) && substr((string)$invoiceNum, 0, 4) === $currDate) {
            $newInvoiceNumber = $invoiceNum + 1;
        } else {
            $newInvoiceNumber = $currDate . '0001';
        }
        return (int)$newInvoiceNumber;
    }
}
