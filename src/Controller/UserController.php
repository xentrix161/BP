<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Order;
use App\Entity\User;
use App\Form\UserType;
use App\Services\FormValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;
    private $formValidationService;
    private $security;
    private $session;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        FormValidationService $formValidationService,
        Security $security,
        SessionInterface $session
    )

    {
        $this->passwordEncoder = $passwordEncoder;
        $this->formValidationService = $formValidationService;
        $this->security = $security;
        $this->session = $session;
    }

    /**
     * Vyrendruje zoznam všetkých používateľov.
     * @Route("/admin/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Vyrendruje formulár na vytvorenie nového používateľa. Skontroluje, či heslo spĺňa bezpečnostné kritéria,
     * zahashuje heslo a pridá použivateľa do databázy.
     * @Route("/admin/new", name="user_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $formData = $form->getData();

            $userName = $formData->getName();
            $userSurname = $formData->getSurname();
            $userEmail = $formData->getEmail();
            $userPass1 = $formData->getPassword();
            $userPass2 = $formData->getPassword2();

            $bool = $this->formValidationService
                ->name($userName)
                ->surname($userSurname)
                ->email($userEmail)
                ->passwordLength($userPass1)
                ->passwordChars($userPass1)
                ->passwordMatch($userPass1, $userPass2)
                ->validate();

            $message = $this->formValidationService->getMessage();

            if ($bool) {
                $temp = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $userEmail]);
                if (empty($temp)) {
                    $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
                    $user->setRole(['ROLE_NONE']);
                    $user->setToken(md5(uniqid()));
                    $user->setTokenDate(new \DateTime());
                    $entityManager->persist($user);
                    $entityManager->flush();
                    return $this->redirectToRoute('user_index');
                } else {
                    $message = "Email sa už používa! Zadajte iný prosím.";
                }
            }

            if (!empty($message)) {
                $this->addFlash(
                    'info',
                    $message
                );
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Zobrazí používateľa poďla ID.
     * @Route("/admin/{id}", name="user_show", methods={"GET"})
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Vyrendruje formulár na edit používateľa podľa ID.
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $temp = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $this->security->getUser()->getUsername()]);

        if (!empty($temp)) {
            if ($temp->getId() !== (int)$request->get('id')
                && $temp->getRole()[0] !== 'ROLE_ADMIN') {
                return $this->redirectToRoute('access_denied');
            }
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $userName = $formData->getName();
            $userSurname = $formData->getSurname();
            $userEmail = $formData->getEmail();
            $userPass1 = $formData->getPassword();
            $userPass2 = $formData->getPassword2();

            $bool = $this->formValidationService
                ->name($userName)
                ->surname($userSurname)
                ->email($userEmail)
                ->passwordLength($userPass1)
                ->passwordChars($userPass1)
                ->passwordMatch($userPass1, $userPass2)
                ->validate();

            $message = $this->formValidationService->getMessage();

            if ($bool) {
                //TODO: if (admin) { }
                //TODO: $temp = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => akutalneZobrazenyUzivatelID]);
                //TODO: else (user) { }
                $temp = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $userEmail]);

                if (empty($temp)) {
                    $user->setPassword($this->passwordEncoder->encodePassword($user, $userPass1));
                    $this->getDoctrine()->getManager()->flush();
                    $message = "Používateľ bol upravený úspešne.";

                    return $this->redirectToRoute('user_index');
                } else {
                    $message = "Email sa už používa! Zadajte iný prosím.";
                }
            }

            if (!empty($message)) {
                $this->addFlash(
                    'info',
                    $message
                );
            }
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Vymaže používateľa podľa ID.
     * @Route("/admin/{id}", name="user_delete", methods={"DELETE"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
                'info',
                'Používateľ bol vymazaný úspešne!'
            );

        }
        return $this->redirectToRoute('user_index');
    }


    /**
     * Vymaže používateľa, ktorý je aktualne prihlásený.
     * Používateľ musí akciu potvrdiť svojím heslom.
     * Ak má používateľ nezaplatené objednávky používateľa nie je možné vymazať
     * Po vymazaní používateľa sa vymažú aj jeho pridané tovary!
     *
     * @Route("/user-account-delete", name="user_account_delete")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse|RedirectResponse
     */
    public function deleteUser(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $data = $request->request->get('delete');
        //TODO: Funguje ale vyhodi akysi error, napriek nemu vsetko prebehne ako má.
        $em = $this->getDoctrine()->getManager();

        $acutalLoggedUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['id' => $data['id']]);
        $actualLoggedUserPassword = $data['password']; //Z FORMULARA
        $actualLoggedUserEmail = $data['email'];

        if ($acutalLoggedUser->getEmail() == $actualLoggedUserEmail) {
            if ($this->passwordEncoder->isPasswordValid($acutalLoggedUser, $actualLoggedUserPassword)) {
                if ($this->isDeletable($acutalLoggedUser)) {
                    $usersArticles = $this->getDoctrine()->getRepository(Article::class)
                        ->findBy(['user_id' => $acutalLoggedUser->getId()]);

                    foreach ($usersArticles as $article) {
                        $em->remove($article);
                    }
                    $em->remove($acutalLoggedUser);
                    $em->flush();

                    $this->get('security.token_storage')->setToken(null);
                    $request->getSession()->invalidate();
                    return new JsonResponse(['success' => true, 'message' => 'Používateľ bol úspešne odstránený.']);
                } else {
                    return new JsonResponse(['success' => false, 'message' => 'Nemáte zaplatené niektoré objednávky, účet nie je možné zmazať.']);
                }
            } else {
                return new JsonResponse(['success' => false, 'message' => 'Nesprávne prihlasovacie údaje. Skúste znovu.']);
            }
        }
        return new JsonResponse(['success' => false, 'message' => 'Nastala neznáma chyba.']);
    }

    /**
     * Ak má vložený používateľ zaplatené všetky objednávky, môže byť vymazaný.
     * @param User $user
     * @return bool
     */
    private function isDeletable(User $user)
    {
        $choosenUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['email' => $user->getEmail()]);

        $usersOrders = $this->getDoctrine()->getRepository(Order::class)
            ->findBy(['user_id' => $choosenUser->getId()]);

        foreach ($usersOrders as $order) {
            if ($order->getPaid() == 0) {
                return false;
            }
        }
        return true;
    }


    /**
     * Vyrendruje stránku pre informácie o profile a úpravu profilu.
     * @Route("/profile", name="profile_info", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function profileInfo(Request $request)
    {
        return $this->render('profileInfo.htlm.twig');
    }
}
