<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale}/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user);
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user);
            //return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
						$this->addFlash('success', "editUser.successMessage");
						return $this->renderForm('user/edit.html.twig', [
							'user' => $user,
							'last_name' => $user->getLastName(),
							'form' => $form,
						]);
        }
        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'last_name' => $user->getLastName(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

		/**
		 * Perform a findOneBy() where the slug property matches {slug}.
		 */
		#[Route('/{username}/editar', name: 'editar_username', methods: ['GET', 'POST'])]
		#[Entity('user', options: ['username' => 'username'])] //le decimos al @ParameterConver que busque en la DB por medio del username
		public function editByUsername(Request $request, User $user, UserRepository $userRepository) {
			$form = $this->createForm(UserType::class, $user);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$userRepository->add($user);
				//return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
				if($request->getLocale() == 'es_ES')
					$this->addFlash('success', "Los datos han sido editados correctamente");
				else
					$this->addFlash('success', "The data has been edited correctly.");
				return $this->renderForm('user/edit.html.twig', [
					'user' => $user,
					'last_name' => $user->getLastName(),
					'form' => $form,
				]);
			}
			return $this->renderForm('user/edit.html.twig', [
				'user' => $user,
				'last_name' => $user->getLastName(),
				'form' => $form,
			]);
		}

		#[Route('/changeLocale', name: 'changeLocale')]
		public function changeLocale  (Request $request): Response
		{
			if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
				$userRepository->remove($user);
			}

			return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
		}

}
