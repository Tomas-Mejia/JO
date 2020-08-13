<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Discipline;
use App\Form\DisciplineType;
use Symfony\Component\HttpFoundation\Request;

class DisciplineController extends AbstractController
{
    /**
     * @Route("/discipline", name="discipline")
     */
    public function index(Request $request)
    {
        // Récupération de Doctrine (connexion à la bdd)
        $em = $this->getDoctrine()->getManager();

        $discipline = new Discipline();
        $form = $this->createForm(DisciplineType::class, $discipline);

        // Analyse la requete
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($discipline); // prépare la sauvegarde
            $em->flush(); // execute la sauvegarde

            $this->addFlash('success', 'Discipline ajouté');
        }

        // Récupération d'une table (discipline)
        $disciplines = $em->getRepository(Discipline::class)->findAll();

        return $this->render('discipline/index.html.twig', [
            'disciplines' => $disciplines,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/discipline/edit/{id}", name="edit_discipline")
     */
    public function edit(Discipline $discipline = null, Request $request){
        if($discipline == null){
            $this->addFlash('danger', 'Discipline introuvable');
            return $this->redirectToRoute('discipline');
        }

        $form = $this->createForm(DisciplineType::class, $discipline);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($discipline);
            $em->flush();

            $this->addFlash('success', 'Discipline mise à jour');
        }

        return $this->render('discipline/edit.html.twig', [
            'discipline' => $discipline,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/discipline/delete/{id}", name="delete_discipline")
     */
    public function delete(Discipline $discipline = null){
        if($discipline == null){
            $this->addFlash('danger', 'Discipline introuvable');
            return $this->redirectToRoute('discipline');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($discipline);
        $em->flush();

        $this->addFlash('warning', 'Discipline supprimé');

        return $this->redirectToRoute('discipline');
    }
}
