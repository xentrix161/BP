<?php


namespace App\Services;

use App\Entity\User;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

class ChartsService
{
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * Vráti všetky TOP štatistiky.
     * @return array[]|null
     */
    public function getTopCharts()
    {
        $outputArray = [
            'TOP 3 predajci' => $this->getTopRatedSellers(),
            'TOP 10 tovarov' => $this->getTopArticles(),
            'TOP 3 profit' => $this->getTopEarners()
        ];

        $empty = false;

        foreach ($outputArray as $item) {
            if (empty($item)) {
                $empty = true;
            } else {
                $empty = false;
            }
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
            $outputArray[] = [
                'name' => $seller->getName() . " " . $seller->getSurname(),
                'data' => $seller->getRating() . " z 5 hviezd",
                'permission' => false
            ];
        }
        return $outputArray;
    }
}