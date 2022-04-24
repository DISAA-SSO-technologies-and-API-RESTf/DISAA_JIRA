<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Repository\AccountRepository;
use App\Repository\UserRepository;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/{_locale}/account')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'app_account_index', methods: ['GET'])]
    public function index(AccountRepository $accountRepository): Response
    {
        return $this->render('account/index.html.twig', [
            'accounts' => $accountRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_account_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AccountRepository $accountRepository): Response
    {
        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountRepository->add($account);
            return $this->redirectToRoute('app_account_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('account/new.html.twig', [
            'account' => $account,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_account_show', methods: ['GET'])]
    public function show(Account $account): Response
    {
        return $this->render('account/show.html.twig', [
            'account' => $account,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_account_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Account $account, AccountRepository $accountRepository): Response
    {
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountRepository->add($account);
            return $this->redirectToRoute('app_account_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('account/edit.html.twig', [
            'account' => $account,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_account_delete', methods: ['POST'])]
    public function delete(Request $request, Account $account, AccountRepository $accountRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$account->getId(), $request->request->get('_token'))) {
            $accountRepository->remove($account);
        }

        return $this->redirectToRoute('app_account_index', [], Response::HTTP_SEE_OTHER);
    }

	#[Route('/{username}/email', name: 'app_email_new', methods: ['GET', 'POST'])]
	#[Entity('user', options: ['username' => 'username'])] //le decimos al @ParameterConver que busque en la DB por medio del username
	public function newEmail(Request $request, User $user, MailerInterface $mailer, UserRepository $userRepository): Response {
		$to = $request->get('to');
		$subject = $request->get('subject');
		$msn = $request->get('msn');
		$sendForAll = $request->get('myCheck');
		//dd($user->getAccount()->getEmail());
		//dd($sendForAll);

		try {
			if ($sendForAll == 'on') {
				$users = $userRepository->findAll();
				foreach ($users as $user) {
					$name_lastName = $user->getName() . " " . $user->getLastName();
					//print("<pre>" . print_r($user->getLastName(), true) . "</pre>");
					if (!is_null($subject) && !is_null($msn) && strcmp($user->getType(), 'Administrative')!=0) {
						$this->email('transferencia.email.20@gmail.com', 'transferencia.email.20@gmail.com', $name_lastName, $subject, $msn, $mailer);
						echo("<script>console.warn('" . $name_lastName . "');</script>");
					}
				}
				$this->addFlash('success', "The messages have been sent successfully.");
			}
			else {
				$name_lastName = $user->getName() . " " . $user->getLastName();
				if (!is_null($subject) && !is_null($msn)) {
					$this->email('transferencia.email.20@gmail.com', 'transferencia.email.20@gmail.com', $name_lastName, $subject, $msn, $mailer);
					$this->addFlash('success', "The message has been sent successfully.");
				}
			}
			return $this->renderForm('email/email.html.twig', [
				/*'account' => $account,*/
			]);
		} catch (Exception $ex) {
			$this->addFlash('error', "The message could not be sent.");
		}
	}

	public function email(string $from, string $to, string $name_lastName, string $subject, string $msn, $mailer) {
		//Declaramos la variable de email
		$email = (new TemplatedEmail())
			->from(new Address($from, 'DISAA MAIL'))
			->to($to)
			->subject($subject)
			->htmlTemplate('email/send_email.html.twig', ["msn" => $msn]);

		//Para enviar variables dentro del template usamos el contexto
		$context = $email->getContext();
		$context['msn'] = $msn;
		$context['to'] = $to;
		$context['name_lastName'] = $name_lastName;
		$email->context($context);

		//Enviamos el correo con send()
		$mailer->send($email);
	}
}
