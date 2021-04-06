<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Form\ArticleType;
use App\Services\ChartsService;
use App\Services\InputValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    private $chartService;
    private $security;
    private $inputValidationService;

    public function __construct(
        ChartsService $chartService,
        Security $security,
        InputValidationService $inputValidationService
    )
    {
        $this->chartService = $chartService;
        $this->security = $security;
        $this->inputValidationService = $inputValidationService;
    }

    /**
     * Vyrendruje zoznam všetkých articlov.
     * @Route("/seller/", name="article_index", methods={"GET"})
     */
    public function index(): Response
    {
        $tempUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->security->getUser()->getUsername()]);

        if (!empty($tempUser->getRole()[0] == 'ROLE_ADMIN')) {
            $articles = $this->getDoctrine()
                ->getRepository(Article::class)
                ->findAll();
        } else {
            $articles = $this->getDoctrine()
                ->getRepository(Article::class)
                ->findBy(['user_id' => $tempUser->getId()]);
        }

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * Vyrendruje article podľa ID.
     * @Route("/produkt/{id}", name="produkt")
     * @param $id
     * @return Response
     */
    public function getArticleDetail($id)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute("access_denied");
        }

        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        $data = $articlesFromDB->findOneBy(['id' => $id]);

        $allCategories = $this->getCategoryList();
        $allCharts = $this->chartService->getTopCharts();

        return $this->render('article/articleDetail.html.twig', [
            'controller_name' => 'ArticleController',
            'categories' => $allCategories,
            'charts' => $allCharts,
            'data' => $data
        ]);
    }

    /**
     * Vyrendruje formulár na vytvorenie nového articlu.
     * @Route("/seller/new", name="article_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $article = new Article();

        $userRepository = $this->getDoctrine()
            ->getRepository(User::class);
        $loggedUser = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);

        $form = $this->createForm(ArticleType::class, $article, [
            'img_is_required' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $title = $formData->getTitle();
            $desc = $formData->getDescription();
            $price = $formData->getPrice();
            $amount = $formData->getAmount();

            $bool = $this->inputValidationService
                ->title($title)
                ->description($desc)
                ->price($price)
                ->amount($amount)
                ->validate();

            $message = $this->inputValidationService->getMessage();
            if (!empty($message)) {
                $this->addFlash('info', $message);
            }

            if ($bool) {
                $catId = $request->request->get('article')['cat_id'];
                $file = $request->files->get('article')['img'];
                $uploads_directory = $this->getParameter('uploads_directory');
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move(
                    $uploads_directory,
                    $fileName
                );

                //Nezaradené
                $category = $this->getDoctrine()->getRepository(Category::class)->find(9);
                // ak nezvolia kategóriu tak nezaradené
                if (empty($catId)) {
                    $article->setCatId($category);
                }

                $article->setImg($fileName);
                $article->setUserId((int)$loggedUser->getId());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($article);
                $entityManager->flush();
                return $this->redirectToRoute('article_index');
            }
            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Zobrazí article podľa ID.
     * @Route("/seller/{id}", name="article_show", methods={"GET"})
     * @param Article $article
     * @param Request $request
     * @return Response
     */
    public function show(Article $article, Request $request): Response
    {
        $tempUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->security->getUser()->getUsername()]);

        $tempArticle = $this->getDoctrine()->getRepository(Article::class)
            ->findOneBy(['id' => (int)$request->get('id')]);

        if (!empty($tempUser)) {
            if ($tempUser->getId() !== $tempArticle->getUserId()
                && $tempUser->getRole()[0] !== 'ROLE_ADMIN') {
                return $this->redirectToRoute('access_denied');
            }
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Vyrendruje formulár na edit articlu podľa ID.
     * @Route("/seller/{id}/edit", name="article_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Article $article
     * @return Response
     */
    public function edit(Request $request, Article $article): Response
    {
        $tempUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->security->getUser()->getUsername()]);

        $tempArticle = $this->getDoctrine()->getRepository(Article::class)
            ->findOneBy(['id' => (int)$request->get('id')]);

        if (!empty($tempUser)) {
            if ($tempUser->getId() !== $tempArticle->getUserId()
                && $tempUser->getRole()[0] !== 'ROLE_ADMIN') {
                return $this->redirectToRoute('access_denied');
            }
        }


        $form = $this->createForm(ArticleType::class, $article, [
            'img_is_required' => false
        ]);
        $form->handleRequest($request);

        $oldImageName = "";

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();

            $title = $formData->getTitle();
            $desc = $formData->getDescription();
            $price = $formData->getPrice();
            $amount = $formData->getAmount();

            $bool = $this->inputValidationService
                ->title($title)
                ->description($desc)
                ->price($price)
                ->amount($amount)
                ->validate();

            $message = $this->inputValidationService->getMessage();
            if (!empty($message)) {
                $this->addFlash('info', $message);
            }

            if ($bool) {
                $file = $request->files->get('article')['img'];
                $oldImageName = $article->getImg();
                try {
                    if (!empty($file)) {
                        $uploads_directory = $this->getParameter('uploads_directory');
                        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                        $file->move(
                            $uploads_directory,
                            $fileName
                        );
                        $article->setImg($fileName);
                        if (!empty($oldImageName) && file_exists($uploads_directory . '/' . $oldImageName)) {
                            unlink($uploads_directory . '/' . $oldImageName);
                        }
                    }
                } catch (\Exception $exception) {
                    //TODO: alert dorobit
                }

                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('article_index');
            }
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'image' => $article->getImg(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Vymaže article podľa ID.
     * @Route("/seller/{id}", name="article_delete", methods={"DELETE"})
     * @param Request $request
     * @param Article $article
     * @return Response
     */
    public function delete(Request $request, Article $article): Response
    {
        $tempUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->security->getUser()->getUsername()]);

        $tempArticle = $this->getDoctrine()->getRepository(Article::class)
            ->findOneBy(['id' => (int)$request->get('id')]);

        if (!empty($tempUser)) {
            if ($tempUser->getId() !== $tempArticle->getUserId()
                && $tempUser->getRole()[0] !== 'ROLE_ADMIN') {
                return $this->redirectToRoute('access_denied');
            }
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }
        return $this->redirectToRoute('article_index');
    }

    public function getCategoryList()
    {
        $categoriesFromDB = $this->getDoctrine()
            ->getRepository(Category::class);
        return $categoriesFromDB->findAll();
    }


    /**
     *
     * @Route("/produkt/{id}/rating", name="article_rating")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function rating($id, Request $request) {

        $isAjax = true;

        if (!$isAjax) {
            $url = $request->getUri();
            $tmp = explode('/', $url);
            array_pop($tmp);
            $url = implode('/', $tmp);
            return $this->render('redirect.htlm.twig', [
                'url' => $url,
            ]);
        }
    }
}
