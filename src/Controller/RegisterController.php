<?php

namespace App\Controller;

use App\Services\FormValidationService;
use App\Services\RoleService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Date;


class RegisterController extends AbstractController
{
    private $passwordEncoder;
    private $formValidationService;
    private $roleService;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        FormValidationService $formValidationService,
        RoleService $roleService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->formValidationService = $formValidationService;
        $this->roleService = $roleService;
    }

    /**
     * Zaregistruje nového používateľa, overí, či je heslo dostatočne bezpečné a pošle aktivačný email.
     * @Route("/registerForm", name="register-form")
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     */
    public function registerU(Request $request, MailerInterface $mailer)
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
                    $token = $this->generateActivateToken();

                    $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
                    $user->setRole([$this->roleService::ROLE_NONE]);
                    $user->setToken($token);
                    $user->setTokenDate(new \DateTime());
                    $user->setActivate(0);
                    $em->persist($user);
                    $em->flush();
                    $message = "Používateľ bol zaregistrovaný. Na Váš email bol zaslaný aktivačný email.";

                    $this->sendEmail($mailer, $userEmail, $token);

                    if (!empty($message)) {
                        $this->addFlash('info', $message);
                    }

                    return $this->redirectToRoute('app_login');
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

        return $this->render('form/index.html.twig', [
            'register_form' => $form->createView(),
        ]);
    }


    /**
     * Overí platnosť aktivačného tokenu a presmeruje na aktivačný formulár.
     * @Route("/activate-account/", name="activate_account")
     * @param $status
     * @return RedirectResponse|Response
     */
    public function accountActivation($status)
    {
//        $tempUser = $this->getDoctrine()->getRepository(User::class)
//            ->findOneBy(['token' => $token]);
//
//        if (empty($tempUser)) {
//            $tempUser = $this->getDoctrine()->getRepository(User::class)
//                ->findOneBy(['email' => $token]);
//        }
//
//        if (empty($tempUser)) {
//            //TODO: Upravit na registraciu
//            return $this->redirectToRoute('shopping_cart');
//        }
//
//        $tokenDateExp = $tempUser->getTokenDate()->modify('+ 7 days');
//        $actualDate = new \DateTime();
//
//        if ($tokenDateExp < $actualDate) {
//            $status = 'tokExp';
//        } elseif ($tempUser->getActivate() == true) {
//            $status = 'success';
//        } else {
//            $status = 'notActivated';
//        }

//        $status = '';
//        if ($token === 'login') {
//            $status = 'notActivated';
//        } else {
//            $tempUser = $this->getDoctrine()->getRepository(User::class)
//                ->findOneBy(['token' => $token]);
//
////            if (empty($tempUser)) {
////                return $this->redirectToRoute('app_homepage');
////            }
//
//            $tokenDateExp = $tempUser->getTokenDate();
//            $actualDate = new \DateTime();
//
////        if ($tempUser->getActivate() == 0 || $token === 'login') {
////            $status = 'notActivated';
////        }
//
//            if ($tokenDateExp < $actualDate) {
//                $status = 'tokExp';
//            }
//
//            elseif ($tempUser->getActivate() == 1) {
//                $status = 'success';
//            }
//        }

        return $this->render('activateAcc.html.twig', [
            'status' => $status,
//            'email' => $tempUser->getEmail(),
        ]);
    }

    /**
     * Vytvorí aktivačný email.
     * @param MailerInterface $mailer
     * @param $email
     * @param $token
     */
    public function sendEmail(MailerInterface $mailer, $email, $token)
    {
        $email = (new TemplatedEmail())
            ->from('filipkosmel@gmail.com')
            ->to($email)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Registrácia - aktivácia účtu')
            ->htmlTemplate('email/register.html.twig')
            ->context([
                'token' => $token
            ]);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
    }

    /**
     * Vygeneruje unikátny token.
     * @return string
     */
    private function generateActivateToken()
    {
        return md5(uniqid());
    }
}