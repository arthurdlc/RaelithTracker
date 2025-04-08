<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    // route pour la page de register, 
    // todo : mettre possibilité de creer un compte avec google
    // todo : faire un formulaire plus propre avec plus de champs 
    // todo mettre en place un regex
    #[Route('/user/new', name: 'app_user_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash le mot de passe
            $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();

            print("User creer");

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // route pour afficher la liste des utilisateur, 
    // todo : mettre cette page reserver au roles : ROLE_ADMIN
    // todo : mettere une mise en page plus propre
    // todo : mettre en place la possibilité aux admin de changer le role d'un user
    // todo : mettre en place la possibilité aux admin de supprimer un user
    // todo : mettre en place la possibilité aux admin de bannir un  user dans ce cas ci un pop up apparaitra et un message de bannissement sera a saisir puis revoyer sous forme de pop up au user lors de sa prochaine connexion
    #[Route('/user', name: 'app_user_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // 🔐 Ajoute cette ligne

        $users = $em->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }
}
