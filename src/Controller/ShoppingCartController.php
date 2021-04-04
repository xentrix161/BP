<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


/**
 * @Route("/shoppingcart")
 */
class ShoppingCartController extends AbstractController
{
    private $session;
    private $security;
    private $mailer;

    public function __construct(SessionInterface $session, Security $security, MailerInterface $mailer)
    {
        $this->session = $session;
        $this->security = $security;
        $this->mailer = $mailer;
    }

    /**
     * Vyrendruje nákupný košík.
     * @Route("/", name="shopping_cart")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function index(Request $request)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('access_denied');
        }
        $error = $request->query->get('error');
        $decoded = [];
        if (!empty($error)) {
            $decoded = explode('-', base64_decode($error));
        }

        return $this->render('shopping_cart/index.html.twig', [
            'controller_name' => 'ShoppingCartController',
            'items' => $this->getItemsFromDbById(),
            'count' => $this->countNumberOfUniqueItems(),
            'totalPrice' => $this->getTotalPrice(),
            'isCartEmpty' => $this->getSessionItems()->isEmpty,
            'notAvailableItems' => $decoded
        ]);
    }

    /**
     * Vyrendruje pokladňu. (Krok po nákupnom košíku)
     * @Route("/cash-desk", name="cash_desk")
     * @param MailerInterface $mailer
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function cashDesk(MailerInterface $mailer, Request $request) {
        $notAvailableItems = $this->checkAvailableItems();

        if (!empty($notAvailableItems)) {

            return $this->redirectToRoute('shopping_cart',
                ['error' => base64_encode(join('-', $notAvailableItems))]);
        }

        $this->mailer = $mailer;

        if (!$this->getUser()) {
            return $this->redirectToRoute('access_denied');
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->security->getUser();
        $user->getUsername();

        $userToUpdateBuyer = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $user->getUsername()]);

        $requestData = $request->request->get('cashdesk');

        if (!is_null($requestData) && !empty($requestData['submit'])) {
            $order = new Order();
            $shopProfit = new Shop();
            $currTime = new \DateTime();
//            $currTime->modify('+ 1 hour');

            $fullAddress = $requestData['address'] . ", " . $requestData['zip'] . ", " . $requestData['city'];

            $newInvoiceNumber = $this->createInvoiceNumber();
            $shopProf = $this->getTotalPrice()*0.05;

            $order->setDate($currTime);
            $order->setAddress($fullAddress);
            $order->setTotalPrice($this->getTotalPrice());
            $order->setUserId($userToUpdateBuyer->getId());
            $order->setMobile($requestData['phone']);
            $order->setInvoiceNumber($newInvoiceNumber);
            $order->setPaymentMethod($requestData['method']);
            $order->setPaid(rand(0,1));
            $em->persist($order);

            $userToUpdateBuyer->setExpense($userToUpdateBuyer->getExpense() + $this->getTotalPrice());

            $em->persist($userToUpdateBuyer);
            $em->flush();

            $tempOrder = $this->getDoctrine()->getRepository(Order::class)
                ->findOneBy(['invoice_number' => $newInvoiceNumber]);

            $shopProfit->setOrderId($tempOrder->getId());
            $shopProfit->setProfit($shopProf);
            $em->persist($shopProfit);

            $this->setExpensesToArticleOwners();
            $em->flush();

            $this->sendEmail($mailer, $order->getInvoiceNumber());

            $this->deleteFullShoppingCart();
        }

        return $this->render('shopping_cart/cash_desk.html.twig', [
            'controller_name' => 'ShoppingCartController',
            'items' => $this->getItemsFromDbById(),
            'count' => $this->countNumberOfUniqueItems(),
            'totalPrice' => $this->getTotalPrice(),
            'isCartEmpty' => $this->getSessionItems()->isEmpty
        ]);
    }

    /**
     * Pridá item do nákupného košíka podľa ID.
     * @Route("/add/{itemId}", name="add_to_cart")
     * @param $itemId
     * @return JsonResponse
     */
    public function addToShoppingCart($itemId)
    {
        $session = $this->getSessionItems();
        $sessionItems = $session->items;

        if (empty($sessionItems)) {
            $sessionItems = [];
        }
        array_push($sessionItems, $itemId);
        $this->session->set($session->name, $sessionItems);

        return new JsonResponse($sessionItems);
    }

    /**
     * Vymaže item z nákupného košíka podľa ID.
     * @Route("/delete/{itemId}", name="delete_from_cart")
     * @param $itemId
     * @return JsonResponse
     */
    public function deleteFromShoppingCart($itemId)
    {
        $session = $this->getSessionItems();
        $sessionItems = $session->items;

        if (!empty($sessionItems)) {
            $temp = [];
            $found = false;
            foreach ($sessionItems as $item) {
                if ($item == $itemId && $found == false) {
                    $found = true;
                } else {
                    array_push($temp, $item);
                }
            }
            $sessionItems = [];
            $sessionItems = array_merge($sessionItems, $temp);
        }
        $this->session->set($session->name, $sessionItems);
        return new JsonResponse($sessionItems);
    }

    /**
     * Vymaže celý nákupný košík.
     * @Route("/delete-cart", name="delete_cart")
     */
    public function deleteFullShoppingCart()
    {
        $this->session->set($this->getSessionItems()->name, []);
        return new JsonResponse(["success" => true]);
    }

    /**
     * Vymaže item z nákupného košíka podľa ID. V prípade, že z daného druhu tovaru je v košíku viac ako 1 kus,
     * vymaže všetky!!!
     * @Route("/delete-item/{itemId}", name="delete_item")
     * @param $itemId
     * @return JsonResponse
     */
    public function deleteItemFromShoppingCartById($itemId)
    {
        $session = $this->getSessionItems();
        $sessionItems = $session->items;
        $temp = [];
        foreach ($sessionItems as $item) {
            if ($item != $itemId) {
                array_push($temp, $item);
            }
        }
        $this->session->set($session->name, $temp);
        return new JsonResponse(["success" => true, "id" => $itemId, "numberOfItems" => count($temp)]);
    }

    /**
     * Vráti zoznam itemov v nákupnom košíku v JSON formáte.
     * @Route("/get", name="get_cart")
     * @return JsonResponse
     */
    public function getShoppingCart()
    {
        return new JsonResponse($this->getSessionItems()->items);
    }

    /**
     * Vráti zoznam itemov v nákupnom košíku.
     * @return object ["name" => $sessionName, "items" => array, "isEmpty" => true/false]
     */
    public function getSessionItems()
    {
        $userEmailSes = $this->security->getUser()->getUsername();
        $sessionItems = $this->session->get($userEmailSes);

        if (empty($sessionItems)) {
            $sessionItems = [];
        }

        $array = ["name" => $userEmailSes, "items" => $sessionItems, "isEmpty" => empty($sessionItems)];
        return (object)$array;
    }

    /**
     * Vráti ID tovaru a k nemu počet kusov v nákupnom košíku.
     * @return array ['article_id' => 'count']
     */
    private function countNumberOfUniqueItems()
    {
        $sessionItems = $this->getSessionItems()->items;
        $outputArray = [];
        foreach ($sessionItems as $item) {
            if (!empty($outputArray[$item])) {
                $outputArray[$item]++;
            } else {
                $outputArray[$item] = 1;
            }
        }
        return $outputArray;
    }

    /**
     * Z databázy vytiahne itemy v nákupnom košíku podľa ID.
     * @return Article[]|object[]
     */
    private function getItemsFromDbById()
    {
        $itemsObject = $this->countNumberOfUniqueItems();

        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        return $articlesFromDB->findBy(['id' => array_keys($itemsObject)]);
    }

    /**
     * Vráti celkovú hodnotu nákupného košíku.
     * @return float|int
     */
    private function getTotalPrice()
    {
        $countedItems = $this->countNumberOfUniqueItems(); //na indexe je ID itemu a v poli je pocet itemov
        $uniqItems = $this->getItemsFromDbById();
        $total = 0;

        if (is_null($uniqItems)) {
            return 0;
        }

        foreach ($uniqItems as $uniqItem) {
            $id = (int)$uniqItem->getId();
            if (empty($id) && $id != 0) {
                continue;
            }
            $count = $countedItems[$id];
            $total += $count * $uniqItem->getPrice();
        }
        return $total;
    }

    /**
     * Vytvorí email pre objednávku.
     * @param MailerInterface $mailer
     * @param int $invoice
     */
    public function sendEmail(MailerInterface $mailer, int $invoice)
    {
        $email = (new TemplatedEmail())
            ->from('filipkosmel@gmail.com')
            ->to($this->getUser()->getUsername())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Objednávka - potvrdenie')
            ->htmlTemplate('email/order.html.twig')
            ->context([
                'totalPrice' => $this->getTotalPrice(),
                'items' => $this->getItemsFromDbById(),
                'count' => $this->countNumberOfUniqueItems(),
                'invoice_number' => $invoice
            ]);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
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

    /**
     * Metóda spracuje údaje v košíku a príslušným majiteľom tovarov pridá profit z predaného tovaru.
     * Metóda tiež upraví počet tovarov na sklade poďla toho, koľko ich bolo v košíku objednaných.
     * @return void
     */
    private function setExpensesToArticleOwners()
    {
        $em = $this->getDoctrine()->getManager();
        $uniqueItemsList =  $this->countNumberOfUniqueItems();

        foreach ($uniqueItemsList as $id => $count) {
            //vytiahnem podla ID z db tovar,
            $article = $this->getDoctrine()->getRepository(Article::class)
                ->findOneBy(['id' => $id]);
            //z tovaru vytiahnem ID usera,
            $userId = $article->getUserId();
            // jednotkovu cenu,
            $pricePerUnit = $article->getPrice();
            // amout tovarov v DB
            $amountOfArticleInDB = $article->getAmount();
            //vytiahnem podla ID z db USERA,
            $userToUpdate = $this->getDoctrine()->getRepository(User::class)
                ->findOneBy(['id' => $userId]);
            // nastavim mu profit podla $count a jednotkovej ceny,
            $profit = $userToUpdate->getEarning() + ($count * $pricePerUnit);
            $userToUpdate->setEarning($profit*0.95);

            $em->persist($userToUpdate);
            // odpocitam amout tovarov podla kosiku,
            $article->setAmount($amountOfArticleInDB - $count);
            // flushnem tovary
            // usera flushnem,
            $em->persist($article);
            $em->flush();
        }
    }

    /**
     * Skontroluje, či sú itemy v nákupnom košíku dostupné.
     * @return array
     */
    private function checkAvailableItems()
    {
        $uniqueItemsList =  $this->countNumberOfUniqueItems();

        $outputArray = [];

        foreach ($uniqueItemsList as $id => $count) {
            $article = $this->getDoctrine()->getRepository(Article::class)
                ->findOneBy(['id' => $id]);

            $amountOfArticleInDB = $article->getAmount();

            if ($amountOfArticleInDB < $count) {
                $outputArray[] = $id;
            }
        }

        return $outputArray;
    }
}
