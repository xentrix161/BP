<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\LoginType;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController
{
    /**
     * @Route("/loginForm", name="login-form")
     * @param Request $request
     * @return Response
     */
    public function loginU(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine();
        $form = $this->createForm(LoginType::class, $user, [
            'action' => $this->generateUrl('login-form')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            $users = $em->getRepository(User::class)->findAll();
//            $users = $em->getRepository(User::class)->findBy('email');
//
//            $this->addFlash(
//                'info',
//                'Užívateľ bol prihlásený!'
//            );
            $email = $form["email"]->getData(); //mail z formulara
            $password = $form["password"]->getData(); //heslo z formulara

            $userFromDB = $this->getDoctrine() //uzivatel z DB podla mailu
                ->getRepository(User::class)
                ->findOneByEmail($email);
            //check hesla
            //TODO: hashovat hesla
            if ($userFromDB->getPassword() === $password) {
                $allArticles = $this->getArticleList();
                return $this->render('homepage.html.twig', [
                    'controller_name' => 'HomepageController',
                    'data' => $allArticles
                ]);
            } else {
                $this->addFlash(
                    'error',
                    'Chyba, zlé heslo alebo mail!'
                );
                return $this->render('login/index.html.twig', [
                    'login_form' => $form->createView()]);
            }
        }

        return $this->render('login/index.html.twig', [
            'login_form' => $form->createView(),
        ]);
    }

    public function getArticleList()
    {
        $articlesFromDB = $this->getDoctrine()
            ->getRepository(Article::class);
        return $articlesFromDB->findAll();
    }
}
