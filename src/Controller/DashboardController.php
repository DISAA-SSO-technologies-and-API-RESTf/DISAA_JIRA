<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DashboardController extends AbstractController
{
    /**
     * @Route("/{_locale}/dashboard", name="dashboard")
     */
    public function index(Request $request)
    {


        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }


    /**
    * @route("/changeLocale", name="changeLocaleS")
    */
    public function changeLocale(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
    	$form = $this->createFormBuilder(null)
    		->add('locale', ChoiceType::class, [
    			'choices' => [
    				'Français' 		=> 'fr_FR',
    				'English(US)'	=> 'en_US'
    			]
    		])
    		->add('save', SubmitType::class)
    		->getForm()
		;

		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$data = $form->getData();
			//dd($data);
			//dd($data->getUsername());

			$locale = $data['locale'];
			//dd($locale);
			$user = $doctrine->getRepository(User::class)->findOneBy(['username' => 'rafael01']);
			//$user = $this->getUser();
			//dd($user);
			$user->setLocale($locale);
			$entityManager->persist($user);
			$entityManager->flush();
		}

    	return $this->render('dashboard/locale.html.twig', [
    		'form'		=> $form->createView()
    	]);
    }

}
