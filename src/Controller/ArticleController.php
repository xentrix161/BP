<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Rating;
use App\Entity\User;
use App\Enum\EntityTypeEnum;
use App\Form\ArticleType;
use App\Services\ChartsService;
use App\Services\InputValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $actualUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->security->getUser()->getUsername()]);

        $userId = $actualUser->getId();
        $entityId = $id;
//        $entityType = EntityTypeEnum::TYPE_ARTICLE;
        $currentTime = new \DateTime();

        $ratingsByArticleId = $this->getDoctrine()->getRepository(Rating::class)
            ->findBy(['entity_id' => $entityId]);
        $max = -1;
        $lastEntityId = null;
        $count = count($ratingsByArticleId);
        $canRate = true;

        if (!empty($ratingsByArticleId)) {
            foreach ($ratingsByArticleId as $index => $rating) {
                if (($rating->getUserId() == $userId) && ($max < $rating->getId())) {
                    $max = $rating->getId();
                    $lastEntityId = $index;
                }
            }

            if (!is_null($lastEntityId) && !empty($ratingsByArticleId[$lastEntityId])) {
                $canRate = $ratingsByArticleId[$lastEntityId]->getDate()->format('n') != $currentTime->format('n');
            }
        }

        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        $data = $articlesFromDB->findOneBy(['id' => $id]);

        $allCategories = $this->getCategoryList();
        $allCharts = $this->chartService->getTopCharts();
        $totalUsers = $this->getNumberOfRegisteredUsers();
        $totalOrders = $this->getNumberOfTotalOrders();

        return $this->render('article/articleDetail.html.twig', [
            'controller_name' => 'ArticleController',
            'categories' => $allCategories,
            'charts' => $allCharts,
            'data' => $data,
            'numberOfRates' => $count,
            'canRate' => $canRate,
            'registeredUsers' => $totalUsers,
            'numberOfTotalOrders' => $totalOrders
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
    public function rating($id, Request $request)
    {
        $newRating = new Rating();
        $em = $this->getDoctrine()->getManager();
        $actualUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->security->getUser()->getUsername()]);

        //aktualny article
        $actualArticle = $this->getDoctrine()->getRepository(Article::class)
            ->findOneBy(['id' => $id]);
        //vsetky jeho ratingy
        $ratingsByArticleId = $this->getDoctrine()->getRepository(Rating::class)
            ->findBy(['entity_id' => $id]);
        //pocet kolko ich je
        $count = count($ratingsByArticleId);
        //sucet hviezdiciek
        $overallRating = 0;
        foreach ($ratingsByArticleId as $rating) {
            $overallRating += $rating->getRate();
        }
        //zoberem kolko ohodnotil prave user
        $valueStars = $request->request->get('data')[0]['value'];
        //pripocitam 1 ku count a pripocitam pocet tohoto hodnotenia ku vsetkym jeho hviezdam
        $count++;
        $overallRating += $valueStars;
        //urobim priemer
        $averageArticleStars = $overallRating / $count;
        //nasetujem ho articlu
        $actualArticle->setRating($averageArticleStars);
        //vytvorim zaznam o hodnoteni
        $newRating->setRate($valueStars);
        $newRating->setDate(new \DateTime());
        $newRating->setUserId((int)$actualUser->getId());
        $newRating->setEntityId($id);
        $newRating->setEntityType(EntityTypeEnum::TYPE_ARTICLE);
        $em->persist($newRating);
        $em->persist($actualArticle);
        $em->flush();

        $owningUser = $this->getDoctrine()->getRepository(User::class)
            ->find($actualArticle->getUserId());
        $this->ratingUser($owningUser);


        $isAjax = $request->request->get('data')[0]['ajax'] == true;
        if (!$isAjax) {
            $url = $request->getUri();
            $tmp = explode('/', $url);
            array_pop($tmp);
            $url = implode('/', $tmp);
            return $this->render('redirect.htlm.twig', [
                'url' => $url,
            ]);
        }
        return new JsonResponse(['success' => true, 'rating' => $overallRating, 'count' => $count]);
    }

    public function getNumberOfRegisteredUsers()
    {
        $registeredUsers = $this->getDoctrine()->getRepository(User::class)
            ->findAll();

        return count($registeredUsers);
    }

    public function getNumberOfTotalOrders()
    {
        $totalOrders = $this->getDoctrine()->getRepository(Order::class)
            ->findAll();
        return count($totalOrders);
    }

    public function ratingUser(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $allUsersArticles = $this->getDoctrine()->getRepository(Article::class)
            ->findBy(['user_id' => $user->getId()]);

        $count = count($allUsersArticles);
        if ($count > 0) {
            $totalValue = 0;
            $usersAvg = 0;
            $counter = 0;
            foreach ($allUsersArticles as $article) {
                if (!is_null($article->getRating()) || $article->getRating() != 0) {
                    $totalValue += $article->getRating();
                    $counter++;
                }
            }
            $usersAvg = $totalValue / $counter;
        }
        $user->setRating($usersAvg);
        $em->persist($user);
        $em->flush();
    }
}
