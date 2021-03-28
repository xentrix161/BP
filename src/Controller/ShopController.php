<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Form\ShopType;
use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shop")
 */
class ShopController extends AbstractController
{
    /**
     * Vyrendruje zoznam všetkých záznamov o portáli.
     * @Route("/", name="shop_index", methods={"GET"})
     * @param ShopRepository $shopRepository
     * @return Response
     */
    public function index(ShopRepository $shopRepository): Response
    {
        return $this->render('shop/index.html.twig', [
            'shops' => $shopRepository->findAll(),
        ]);
    }

    /**
     * Vyrendruje formulár na vytvorenie nového záznamu o portáli.
     * @Route("/admin/new", name="shop_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $shop = new Shop();
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($shop);
            $entityManager->flush();

            return $this->redirectToRoute('shop_index');
        }

        return $this->render('shop/new.html.twig', [
            'shop' => $shop,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Zobrazí záznam o portáli podľa ID.
     * @Route("/admin/{id}", name="shop_show", methods={"GET"})
     * @param Shop $shop
     * @return Response
     */
    public function show(Shop $shop): Response
    {
        return $this->render('shop/show.html.twig', [
            'shop' => $shop,
        ]);
    }

    /**
     * Vyrendruje formulár na edit záznamu o portáli podľa ID.
     * @Route("/admin/{id}/edit", name="shop_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Shop $shop
     * @return Response
     */
    public function edit(Request $request, Shop $shop): Response
    {
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('shop_index');
        }

        return $this->render('shop/edit.html.twig', [
            'shop' => $shop,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Vymaže záznam o portáli podľa ID.
     * @Route("/admin/{id}", name="shop_delete", methods={"DELETE"})
     * @param Request $request
     * @param Shop $shop
     * @return Response
     */
    public function delete(Request $request, Shop $shop): Response
    {
        if ($this->isCsrfTokenValid('delete'.$shop->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($shop);
            $entityManager->flush();
        }

        return $this->redirectToRoute('shop_index');
    }
}
