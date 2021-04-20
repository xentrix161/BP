<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\ShoppingCart;
use App\Entity\User;
use App\Form\ShoppingCartType;
use App\Repository\ShoppingCartRepository;
use App\Services\FormValidationService;
use App\Services\InputValidationService;
use App\Services\PersonalizationDataService;
use App\Services\ShoppingCartService;
use DateTime;
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
    private $inputValidationService;
    private $formValidationService;
    private $shoppingCartService;
    private $personalizationDataService;

    public function __construct(
        SessionInterface $session,
        Security $security, MailerInterface $mailer,
        InputValidationService $inputValidationService,
        FormValidationService $formValidationService,
        ShoppingCartService $shoppingCartService,
        PersonalizationDataService $personalizationDataService
    )
    {
        $this->session = $session;
        $this->security = $security;
        $this->mailer = $mailer;
        $this->inputValidationService = $inputValidationService;
        $this->formValidationService = $formValidationService;
        $this->shoppingCartService = $shoppingCartService;
        $this->personalizationDataService = $personalizationDataService;
    }

    /**
     * Vyrendruje nákupný košík.
     * @Route("/", name="shopping_cart")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function index(Request $request)
    {
//        $this->shoppingCartService->countNumberOfAllSoldItemsSorted(); die;
        if (!$this->getUser()) {
            return $this->redirectToRoute('access_denied');
        }
        $error = $request->query->get('error');
        $decoded = [];
        if (!empty($error)) {
            $decoded = explode('-', base64_decode($error));
        }


        $suggestedArticles = $this->getSuggestedArticles();
        return $this->render('shopping_cart/index.html.twig', [
            'controller_name' => 'ShoppingCartController',
            'items' => $this->getItemsFromDbById(),
            'count' => $this->shoppingCartService->countNumberOfUniqueItems(),
            'totalPrice' => $this->getTotalPrice(),
            'isCartEmpty' => $this->shoppingCartService->getSessionItems()->isEmpty,
            'notAvailableItems' => $decoded,
            'suggestedArticles' => $suggestedArticles
        ]);
    }

    /**
     * Vyrendruje pokladňu. (Krok po nákupnom košíku)
     * @Route("/cash-desk", name="cash_desk")
     * @param MailerInterface $mailer
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function cashDesk(MailerInterface $mailer, Request $request)
    {
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
        $userToUpdateBuyer = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $user->getUsername()]);

        $requestData = $request->request->get('cashdesk');

        if (!is_null($requestData) && !empty($requestData['submit'])) {
            $name = $requestData['name'];
            $surname = $requestData['surname'];
            $street = $requestData['address'];
            $city = $requestData['city'];
            $zip = $requestData['zip'];
            $mobileNumber = $requestData['phone'];

            $boolForm = $this->formValidationService
                ->name($name)
                ->surname($surname)
                ->validate();

            $boolInput = $this->inputValidationService
                ->street($street)
                ->city($city)
                ->zip($zip)
                ->mobileNumber($mobileNumber)
                ->validate();

            $message = $this->formValidationService->getMessage();

            if (empty($message)) {
                $message = $this->inputValidationService->getMessage();
            }
            if (!empty($message)) {
                $this->addFlash('info', $message);
            }
            $bool = $boolForm && $boolInput;

            if ($bool) {
                $order = new Order();
                $shopProfit = new Shop();
                $currTime = new DateTime();

                $fullAddress = $requestData['address'] . ", " . $requestData['zip'] . ", " . $requestData['city'];

                $newInvoiceNumber = $this->createInvoiceNumber();
                $shopProf = $this->getTotalPrice() * 0.05;

                $order->setDate($currTime);
                $order->setAddress($fullAddress);
                $order->setTotalPrice($this->getTotalPrice());
                $order->setUserId($userToUpdateBuyer->getId());

                $phoneNumber = str_replace(' ', '', $requestData['phone']);
                $phoneNumber = preg_replace('/^(00)/', '+', $phoneNumber);

                $order->setMobile($phoneNumber);
                $order->setInvoiceNumber($newInvoiceNumber);
                $order->setPaymentMethod($requestData['method']);
                $payResult = rand(0, 1);
                $order->setPaid($payResult);

                if ($payResult == 0) {
                    $this->shoppingCartService
                        ->setOwnerId($this->getUserId())
                        ->setStatus($this->shoppingCartService::STATUS_PAYMENT)
                        ->update();
                } else {
                    $this->shoppingCartService->setStatus($this->shoppingCartService::STATUS_DONE);
                }

                $em->persist($order);

                $userToUpdateBuyer->setExpense($userToUpdateBuyer->getExpense() + $this->getTotalPrice());

                $em->persist($userToUpdateBuyer);
                $em->flush();

                $tempOrder = $this->getDoctrine()->getRepository(Order::class)
                    ->findOneBy(['invoice_number' => $newInvoiceNumber]);

                $shopProfit->setOrderId($tempOrder->getId());
                $shopProfit->setProfit($shopProf);
                $em->persist($shopProfit);
                $this->setProfitToArticleOwners();
                $em->flush();

                $this->sendEmail($mailer, $order->getInvoiceNumber());
                $this->deleteFullShoppingCart(true);
            }
        }

        return $this->render('shopping_cart/cash_desk.html.twig', [
            'controller_name' => 'ShoppingCartController',
            'items' => $this->getItemsFromDbById(),
            'count' => $this->shoppingCartService->countNumberOfUniqueItems(),
            'totalPrice' => $this->getTotalPrice(),
            'isCartEmpty' => $this->shoppingCartService->getSessionItems()->isEmpty
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
        $sessionItems = $this->shoppingCartService->setSessionItem($itemId);
        $this->shoppingCartService
            ->setOwnerId($this->getUserId())
            ->setContent($sessionItems)
            ->setTotalPrice($this->getTotalPrice())
            ->update();
        return new JsonResponse($sessionItems);
    }

    /**
     * Vymaže jeden item z nákupného košíka podľa ID.
     * @Route("/delete/{itemId}", name="delete_from_cart")
     * @param $itemId
     * @return JsonResponse
     */
    public function deleteFromShoppingCart($itemId)
    {
        $cartItems = $this->shoppingCartService->deleteFromShoppingCart($itemId);

        $this->shoppingCartService
            ->setOwnerId($this->getUserId())
            ->setContent($cartItems)
            ->setTotalPrice($this->getTotalPrice())
            ->update();
        return new JsonResponse($cartItems);
    }

    /**
     * Vymaže celý nákupný košík.
     * @Route("/delete-cart", name="delete_cart")
     */
    public function deleteFullShoppingCart($bool = false)
    {
        if (!$bool) {
            $this->shoppingCartService
                ->setOwnerId($this->getUserId())
                ->setContent([])
                ->setTotalPrice(0)
                ->update();
        }

        $this->session->set($this->shoppingCartService->getSessionItems()->name, []);
        return new JsonResponse(["success" => true]);
    }

    /**
     * Vymaže vsetky item z nákupného košíka podľa ID. V prípade, že z daného druhu tovaru je v košíku viac ako 1 kus,
     * vymaže všetky!!!
     * @Route("/delete-item/{itemId}", name="delete_item")
     * @param $itemId
     * @return JsonResponse
     */
    public function deleteItemFromShoppingCartById($itemId)
    {
        $cartItems = $this->shoppingCartService->deleteItemFromShoppingCartById($itemId);

        $this->shoppingCartService
            ->setOwnerId($this->getUserId())
            ->setContent($cartItems['cartItems'])
            ->setTotalPrice($this->getTotalPrice())
            ->update();

        return new JsonResponse($cartItems);
    }

    /**
     * Vráti zoznam itemov v nákupnom košíku v JSON formáte.
     * @Route("/get", name="get_cart")
     * @return JsonResponse
     */
    public function getShoppingCart()
    {
        return new JsonResponse($this->shoppingCartService->getSessionItems()->items);
    }

    /**
     * Z databázy vytiahne itemy v nákupnom košíku podľa ID.
     * @return Article[]|object[]
     */
    private function getItemsFromDbById()
    {
        $itemsObject = $this->shoppingCartService->countNumberOfUniqueItems();

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
        $countedItems = $this->shoppingCartService->countNumberOfUniqueItems(); //na indexe je ID itemu a v poli je pocet itemov
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
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Objednávka - potvrdenie')
            ->htmlTemplate('email/order.html.twig')
            ->context([
                'totalPrice' => $this->getTotalPrice(),
                'items' => $this->getItemsFromDbById(),
                'count' => $this->shoppingCartService->countNumberOfUniqueItems(),
                'invoice_number' => $invoice
            ]);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
    }

    /**
     * @Route("/create-cart-row", name="create_cart_row")
     */
    public function createCartRow()
    {
        $this->shoppingCartService
            ->setOwnerId($this->getUserId())
            ->setContent([])
            ->setTotalPrice(0)
            ->create();

        return $this->render('redirect.htlm.twig');
    }

    /**
     * Vytvorí unikátne číslo faktúry.
     * @return int
     */
    private function createInvoiceNumber()
    {
        $currDate = date("ym");

        $lastInvoiceThisMonth = $this->getDoctrine()->getRepository(Order::class)
            ->findBy(array(), array('invoice_number' => 'DESC'), 1, 0);
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
    private function setProfitToArticleOwners()
    {
        $em = $this->getDoctrine()->getManager();
        $uniqueItemsList = $this->shoppingCartService->countNumberOfUniqueItems();

        foreach ($uniqueItemsList as $id => $count) {
            $article = $this->getDoctrine()->getRepository(Article::class)
                ->findOneBy(['id' => $id]);
            $userId = $article->getUserId();
            $pricePerUnit = $article->getPrice();
            $amountOfArticleInDB = $article->getAmount();
            $userToUpdate = $this->getDoctrine()->getRepository(User::class)
                ->findOneBy(['id' => $userId]);
            $profit = $userToUpdate->getEarning() + ($count * $pricePerUnit);
            $userToUpdate->setEarning($profit * 0.95);

            $em->persist($userToUpdate);
            $article->setAmount($amountOfArticleInDB - $count);
            $em->persist($article);
            $em->flush();
        }
    }

    public function getSuggestedArticles()
    {
        return $this->personalizationDataService->getAdvertisedArticles();
    }

    /**
     * Skontroluje, či sú itemy v nákupnom košíku dostupné.
     * @return array
     */
    private function checkAvailableItems()
    {
        $uniqueItemsList = $this->shoppingCartService->countNumberOfUniqueItems();

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

    private function getUserId()
    {
        $user = $this->security->getUser();
        $actualUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $user->getUsername()]);

        return $actualUser->getId();
    }

    /**
     * @Route("/admin/", name="shopping_cart_index", methods={"GET"})
     */
    public function index1(ShoppingCartRepository $shoppingCartRepository): Response
    {
        return $this->render('shopping_cart_cont/index.html.twig', [
            'shopping_carts' => $shoppingCartRepository->findAll(),
        ]);
    }

//    /**
//     * @Route("/new", name="shopping_cart_cont_new", methods={"GET","POST"})
//     */
//    public function new(Request $request): Response
//    {
//        $shoppingCart = new ShoppingCart();
//        $form = $this->createForm(ShoppingCartType::class, $shoppingCart);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($shoppingCart);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('shopping_cart_cont_index');
//        }
//
//        return $this->render('shopping_cart_cont/new.html.twig', [
//            'shopping_cart' => $shoppingCart,
//            'form' => $form->createView(),
//        ]);
//    }

    /**
     * @Route("/{id}", name="shopping_cart_show", methods={"GET"})
     */
    public function show(ShoppingCart $shoppingCart): Response
    {
        return $this->render('shopping_cart_cont/show.html.twig', [
            'shopping_cart' => $shoppingCart,
        ]);
    }

//    /**
//     * @Route("/{id}/edit", name="shopping_cart_cont_edit", methods={"GET","POST"})
//     */
//    public function edit(Request $request, ShoppingCart $shoppingCart): Response
//    {
//        $form = $this->createForm(ShoppingCartType::class, $shoppingCart);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $this->getDoctrine()->getManager()->flush();
//
//            return $this->redirectToRoute('shopping_cart_cont_index');
//        }
//
//        return $this->render('shopping_cart_cont/edit.html.twig', [
//            'shopping_cart' => $shoppingCart,
//            'form' => $form->createView(),
//        ]);
//    }
//
//    /**
//     * @Route("/{id}", name="shopping_cart_cont_delete", methods={"POST"})
//     */
//    public function delete(Request $request, ShoppingCart $shoppingCart): Response
//    {
//        if ($this->isCsrfTokenValid('delete'.$shoppingCart->getId(), $request->request->get('_token'))) {
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->remove($shoppingCart);
//            $entityManager->flush();
//        }
//
//        return $this->redirectToRoute('shopping_cart_cont_index');
//    }
}
