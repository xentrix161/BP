<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Services\InputValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/category")
 */
class CategoryController extends AbstractController
{
    private $inputValidationService;

    public function __construct(InputValidationService $inputValidationService)
    {
        $this->inputValidationService = $inputValidationService;
    }


    /**
     * Vyrendruje zoznam všetkých kategórií.
     * @Route("/admin/", name="category_index", methods={"GET"})
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * Vyrendruje formulár na vytvorenie novej kategórie.
     * @Route("/admin/new", name="category_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $title = $formData->getName();
//            $slug = $formData->getSlug();


            $bool = $this->inputValidationService
                ->title($title)
//                ->slug($slug)
                ->validate();

            $message = $this->inputValidationService->getMessage();
            if (!empty($message)) {
                $this->addFlash('info', $message);
            }

            if ($bool) {
                $entityManager = $this->getDoctrine()->getManager();
                $slug = str_replace(' ', '-', $title);
                $slug = $this->inputValidationService->normalize($slug);

                if (strlen($slug) < 3) {
                    $slug .= '-slug';
                }

                $slug = strtolower($slug);
                $category->setSlug($slug);

                $entityManager->persist($category);
                $entityManager->flush();
                return $this->redirectToRoute('category_index');
            }
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Zobrazí kategóriu podľa ID.
     * @Route("/admin/{id}", name="category_show", methods={"GET"})
     * @param Category $category
     * @return Response
     */
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * Vyrendruje formulár na edit kategórie podľa ID.
     * @Route("/admin/{id}/edit", name="category_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $title = $formData->getName();
            $slug = $formData->getSlug();

            $bool = $this->inputValidationService
                ->title($title)
                ->slug($slug)
                ->validate();

            $message = $this->inputValidationService->getMessage();
            if (!empty($message)) {
                $this->addFlash('info', $message);
            }

            if ($bool) {
                $slug = str_replace(' ', '-', $slug);
                $slug = $this->inputValidationService->normalize($slug);
                $slug = strtolower($slug);
                $category->setSlug($slug);

                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('category_index');
            }
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Vymaže kategóriu podľa ID.
     * @Route("/admin/{id}", name="category_delete", methods={"DELETE"})
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('category_index');
    }
}
