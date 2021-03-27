<?php


namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ChartsService
{

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

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

    public function getTopEarners($numberOfItems = 3)
    {
        $top3Earners = $this->em->getRepository(User::class)
            ->findBy(array(), array('earning' => 'DESC'), $numberOfItems, 0);

        $outputArray = [];
        foreach ($top3Earners as $earner) {
            $outputArray[] = [
                'name' => $earner->getName() . " " . $earner->getSurname(),
                'data' => $earner->getEarning() . "€"
            ];
        }
        return $outputArray;
    }

    public function getTopArticles($numberOfItems = 10)
    {
        return [];
    }

    public function getTopRatedSellers($numberOfItems = 3)
    {
        $top3RatedSellers = $this->em->getRepository(User::class)
            ->findBy(array(), array('rating' => 'DESC'), $numberOfItems, 0);

        $outputArray = [];
        foreach ($top3RatedSellers as $seller) {
            $outputArray[] = [
                'name' => $seller->getName() . " " . $seller->getSurname(),
                'data' => $seller->getRating() . "*"
            ];
        }
        return $outputArray;
    }
}