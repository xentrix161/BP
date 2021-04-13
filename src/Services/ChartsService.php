<?php


namespace App\Services;

use App\Entity\User;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChartsService extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Vráti všetky TOP štatistiky.
     * @return array[]|null
     */
    public function getTopCharts()
    {
        $outputArray = [
            'TOP hodnotení predajci' => $this->getTopRatedSellers(),
            'TOP hodnotené tovary' => $this->getTopArticles(),
            'TOP profitový predajci' => $this->getTopEarners(),
//            'TOP predávané tovary' => $this->getTopSoldArticles()

        ];

        $empty = false;
        foreach ($outputArray as $item) {
            $empty = empty($item);
        }
        return $empty ? null : $outputArray;
    }


    /**
     * Vráti TOP x zarabajúcich predajcov celkovo.
     * @param int $numberOfItems
     * @return array
     */
    public function getTopEarners($numberOfItems = 3)
    {
        $top3Earners = $this->em->getRepository(User::class)
            ->findBy(array(), array('earning' => 'DESC'), $numberOfItems, 0);

        $outputArray = [];
        foreach ($top3Earners as $earner) {
            $outputArray[] = [
                'name' => $earner->getName() . " " . $earner->getSurname(),
                'data' => round($earner->getEarning(), 2) . "€",
                'entity' => $earner,
                'url' => $this->generateUrl('user_articles', ['id' => $earner->getId()]),
                'permission' => true
            ];
        }
        return $outputArray;
    }

    /**
     * Vráti TOP x hodnotených articlov celkovo.
     * @param int $numberOfItems
     * @return array
     */
    public function getTopArticles($numberOfItems = 10)
    {
        $top10RatedArticles = $this->em->getRepository(Article::class)
            ->findBy(array(), array('rating' => 'DESC'), $numberOfItems, 0);

        $outputArray = [];
        foreach ($top10RatedArticles as $article) {
            if ($article->getRating() != null) {
                $outputArray[] = [
                    'name' => $article->getTitle(),
                    'data' => round($article->getRating(), 2) . " z 5 hviezd",
                    'entity' => $article,
                    'url' => $this->generateUrl('produkt', ['id' => $article->getId()]),
                    'permission' => true
                ];
            }
        }
        return $outputArray;
    }

    /**
     * Vráti TOP x hodnotených articlov celkovo.
     * @param int $numberOfItems
     * @return array
     */
    public function getTopSoldArticles($numberOfItems = 10)
    {
        $top10SoldArticles = $this->em->getRepository(Article::class)
            ->findBy(array(), array('sold' => 'DESC'), $numberOfItems, 0);

        $outputArray = [];
        foreach ($top10SoldArticles as $article) {
            if ($article->getSold() != null) {
                $outputArray[] = [
                    'name' => $article->getTitle(),
                    'data' => $article->getSold() . " predaných",
                    'entity' => $article,
                    'url' => '#',
                    'permission' => true
                ];
            }
        }
        return $outputArray;
    }


    /**
     * Vráti TOP x hodnotených predajcov celkovo.
     * @param int $numberOfItems
     * @return array
     */
    public function getTopRatedSellers($numberOfItems = 3)
    {
        $top3RatedSellers = $this->em->getRepository(User::class)
            ->findBy(array(), array('rating' => 'DESC'), $numberOfItems, 0);

        $outputArray = [];
        foreach ($top3RatedSellers as $seller) {
            if ($seller->getRating() != null) {
                $outputArray[] = [
                    'name' => $seller->getName() . " " . $seller->getSurname() . ", ID: " . $seller->getId(),
                    'data' => $seller->getRating() . " z 5 hviezd",
                    'entity' => $seller,
                    'url' => $this->generateUrl('user_articles', ['id' => $seller->getId()]),
                    'permission' => false
                ];
            }
        }
        return $outputArray;
    }
}