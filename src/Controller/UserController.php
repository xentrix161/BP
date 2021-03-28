<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Services\FormValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    public function __construct(UserPasswordEncoderInterface $passwordEncoder,
                                FormValidationService $formValidationService,
                                Security $security)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->formValidationService = $formValidationService;
        $this->security = $security;
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
                    $message = "Používateľ bol vytvorený úspešne.";
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
     * Vyrendruje stránku pre informácie o profile a úpravu profilu.
     * @Route("/profile", name="profile_info", methods={"GET"})
     * @return Response
     */
    public function profileInfo()
    {
        return $this->render('profileInfo.htlm.twig');
    }
}
