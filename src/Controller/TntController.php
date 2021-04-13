<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\User;
use App\Services\ChartsService;
use App\Services\RoleService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;
use TeamTNT\TNTSearch\Stemmer\PorterStemmer;
use TeamTNT\TNTSearch\TNTSearch;

class TntController extends AbstractController
{

    private $limitArticlesPerPage = 20;
    private $security;
    private $roleService;
    private $chartService;

    public function __construct(Security $security, RoleService $roleService, ChartsService $chartService)
    {
        $this->security = $security;
        $this->roleService = $roleService;
        $this->chartService = $chartService;
    }

    /**
     * Vyrendruje homepage portálu.
     * @Route("/search-results", name="search_results")
     * @param Request $request
     * @return Response
     * @throws IndexNotFoundException
     */
    public function index(Request $request): Response
    {
        $searchWord = $request->query->get('search');
        $foundIds = $this->search($searchWord);

        $allArticles = $this->getArticleList($foundIds);
        $allCategories = $this->getCategoryList();
        $allCharts = $this->chartService->getTopCharts();
        $registeredUsers = $this->getNumberOfRegisteredUsers();
        $totalOrders = $this->getNumberOfTotalOrders();
        return $this->render('tnt/index.html.twig', [
            'controller_name' => 'TntController',
            'data' => $allArticles,
            'categories' => $allCategories,
            'categoryBool' => false,
            'charts' => $allCharts,
            'registeredUsers' => $registeredUsers,
            'numberOfTotalOrders' => $totalOrders
        ]);
    }

    /**
     * @Route("/generate-index", name="app_generate-index")
     */
    public function generate_index()
    {
        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $configuration = $this->getTNTSearchConfiguration();
        $tnt->loadConfig($configuration);

        // The index file will have the following name, feel free to change it as you want
        $indexer = $tnt->createIndex('article.index');

        // The result with all the rows of the query will be the data
        // that the engine will use to search, in our case we only want 2 columns
        // (note that the primary key needs to be included)
        $indexer->query('SELECT * FROM article;');

        // Generate index file !
        $indexer->run();

        return new Response(
            '<html><body>Index succesfully generated !</body></html>'
        );
    }

    /**
     * Returns an array with the configuration of TNTSearch with the
     * database used by the Symfony project.
     *
     * @return array
     */
    private function getTNTSearchConfiguration()
    {
        $databaseURL = $_ENV['DATABASE_URL'];

        $databaseParameters = parse_url($databaseURL);

        $config = [
            'driver' => $databaseParameters["scheme"],
            'host' => $databaseParameters["host"],
            'database' => 'mydb',
            'username' => 'admin',
            'password' => 'bakalarka123',
            'storage' => 'C:\Users\filip\Desktop\BP\fuzzy_storage',
            'stemmer' => PorterStemmer::class
        ];
        return $config;
    }

    /**
     * @param $searchWord
     * @return Article[]|object[]
     * @throws IndexNotFoundException
     */
    public function search($searchWord)
    {
        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $configuration = $this->getTNTSearchConfiguration();
        $tnt->loadConfig($configuration);

        // Use the generated index in the previous step
        $tnt->selectIndex('article.index');

        $maxResults = 20;

        // Search for a band named like "Guns n' roses"
        $results = $tnt->search($searchWord, $maxResults);

        $em = $this->getDoctrine()->getManager();

        $articleRepository = $em->getRepository(Article::class);

        return $articleRepository->findBy(['id' => $results['ids']]);
    }

    /**
     * Vráti list všetkých articlov. (Bez zvolenej kategórie)
     * @param $ids
     * @param int $pageNumber
     * @return Article[]|object[]
     */
    public function getArticleList($ids)
    {
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        return $articlesFromDB->findBy(['id' => $ids]);
    }

    /**
     * Vráti všetky kategórie.
     * @return Category[]|array
     */
    public function getCategoryList()
    {
        $categoriesFromDB = $this->getDoctrine()
            ->getRepository(Category::class);
        return $categoriesFromDB->findAll();
    }

    /**
     * Vygeneruje číslovanie stránky bez zvolenej kategórie.
     * @param $pageNumber
     * @return array
     */
    public function generatePaginationBar($pageNumber)
    {
        $totalPages = $this->getTotalPages();
        return $this->pagination($pageNumber, $totalPages);
    }

    /**
     * Vráti pole obsahujúce dáta, ktoré určujú ako bude vypadať čislovací bar na stránke.
     * @param $pageNum
     * @param $totalPages
     * @return array
     */
    private function pagination($pageNum, $totalPages)
    {
        $current = $pageNum;
        $last = $totalPages;
        $range = array();
        $rangeWithDots = array();

        $delta = 1; //pocet + - stranok od currentPage
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $l = -1;

        for ($i = 1; $i <= $last; $i++) {
            if ($i == 1 || $i == $last || $i >= $left && $i < $right) {
                array_push($range, $i);
            }
        }

        for ($i = 0; $i < count($range); $i++) {
            if ($l != -1) {
                if ($range[$i] - $l === 2) {
                    array_push($rangeWithDots, $l + 1);
                } else if ($range[$i] - $l !== 1) {
                    array_push($rangeWithDots, 0);
                }
            }
            array_push($rangeWithDots, $range[$i]);
            $l = $range[$i];
        }
        return $rangeWithDots;
    }

    /**
     * Vráti počet potrebných strán na vykreslenie všetkých articlov.
     * @return false|float
     */
    public function getTotalPages()
    {
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        $all = $articlesFromDB->findAll();
        $totalNumberOfArticles = count($all);
        return ceil($totalNumberOfArticles / $this->limitArticlesPerPage);
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
}
