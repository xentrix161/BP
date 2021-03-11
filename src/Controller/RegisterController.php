<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegisterController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    //HOTOVO

    /**
     * @Route("/registerForm", name="register-form")
     * @param Request $request
     * @return Response
     */
    public function registerU(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(RegisterType::class, $user, [
            'action' => $this->generateUrl('register-form')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $flashMessage = "";
            //ulozenie do DB
            $formData = $form->getData();

            $userName = $formData->getName();
            $userSurname = $formData->getSurname();
            $userEmail = $formData->getEmail();
            $userPass1 = $formData->getPassword();
            $userPass2 = $formData->getPassword2();
            $userRole = $formData->getRole();

            $lowerCase = preg_match('@[a-z]@', $userPass1);
            $upperCase = preg_match('@[A-Z]@', $userPass1);
            $number = preg_match('@[0-9]@', $userPass1);

            if (strlen($userName) < 3 || strlen($userSurname) < 3) {
                $flashMessage = "Meno alebo priezvisko je príliš krátke. Musí obsahovať aspoň 3 znaky!";
            } elseif (!preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$/', $userEmail)) {
                $flashMessage = "Váš email je zadaný v nesprávnom tvare!";
            } elseif (strlen($userPass1) < 6) {
                $flashMessage = "Heslo je príliš krátke. Musí obsahovať aspoň 6 znakov!";

            } elseif (!$lowerCase || !$upperCase || !$number) {
                $flashMessage = "Heslo musí obsahovať aspoň 1 veľké písmeno, 1 malé písmeno a jedno číslo!";

            } elseif ($userPass1 != $userPass2) {
                $flashMessage = "Vaše heslo sa nezhoduje s kontrolným heslom. Heslá sa musia zhodovať!";
            } else {
                $temp = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $userEmail]);
                if (empty($temp) || is_null($temp)) {
                    $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
                    $user->setRole(['ROLE_USER']);
                    $em->persist($user);
                    $em->flush();
                    $flashMessage = "Používateľ bol zaregistrovaný";
                } else {
                    $flashMessage = "Email sa už používa! Zadajte iný";
                }
            }

            $this->addFlash(
                'info',
                $flashMessage
            );
        }

        return $this->render('form/index.html.twig', [
            'register_form' => $form->createView()
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


//    /**
//    //     * @Route("/formDel", name="formDel")
//    //     */
//    public function removeU(Request $request)
//    {
//        $user = new User();
//        $em = $this->getDoctrine()->getManager();
//        $form = $this->createForm(RegisterType::class, $user, [
//            'action' => $this->generateUrl('formDel')
//        ]);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $userToRemove = $em->getRepository(User::class)->findOneBy([
//                'email' => $user->getEmail()
//            ]);
//            $em->remove($userToRemove);
//            $em->flush();
//        }
//
//        return $this->render('form/index.html.twig', [
//            'post_form' => $form->createView()
//        ]);
//    }
//
//    /**
//     * @Route("/formUpd", name="formUpd")
//     */
//    public function updateU(Request $request)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $userToUpadate = $em->getRepository(User::class)->findOneBy([
//            'email' => 'update@gmail.com'
//        ]);
//
//        $form = $this->createForm(RegisterType::class, $userToUpadate, [
//            'action' => $this->generateUrl('formUpd')
//        ]);
//        $form->handleRequest($request);
//
//        $userToUpadate->setName("updatovany");
//        $em->persist($userToUpadate);
//        $em->flush();
//
//        return $this->render('form/index.html.twig', [
//            'post_form' => $form->createView()
//        ]);
//    }
}