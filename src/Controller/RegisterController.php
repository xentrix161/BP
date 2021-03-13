<?php

namespace App\Controller;

use App\Services\FormValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegisterController extends AbstractController
{
    private $roleTypes = [['["ROLE_USER"]', 'Nákupca'], ['["ROLE_SELLER"]', 'Predajca']];
    private $passwordEncoder;
    private $formValidationService;


    public function __construct(UserPasswordEncoderInterface $passwordEncoder, FormValidationService $formValidationService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->formValidationService = $formValidationService;
    }

    /**
     * @Route("/registerForm", name="register-form")
     * @param Request $request
     * @return Response
     */
    public function registerU(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(RegisterType::class, $user, [
            'action' => $this->generateUrl('register-form')
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //ulozenie do DB
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
                    $em->persist($user);
                    $em->flush();
                    $message = "Používateľ bol zaregistrovaný";
                } else {
                    $message = "Email sa už používa! Zadajte iný";
                }
            }

            if (!empty($message)) {
                $this->addFlash(
                    'info',
                    $message
                );
            }
        }

        return $this->render('form/index.html.twig', [
            'register_form' => $form->createView(),
            'user_roles' => $this->roleTypes
        ]);
    }

    /**
     * @Route("/formCout", name="formCout")
     * @param Request $request
     */
    public
    function showUserTable(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        dump($users);
        exit;
    }
}