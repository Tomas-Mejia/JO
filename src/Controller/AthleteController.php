<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Athlete;
use App\Form\AthleteType;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AthleteController extends AbstractController
{

    /**
     * @Route("/athlete", name="athlete")
     */
    public function index(Request $request)
    {
        // Récupération de Doctrine (connexion à la bdd)
        $em = $this->getDoctrine()->getManager();

        $athlete = new Athlete();
        $form = $this->createForm(AthleteType::class, $athlete);

        // Analyse la requete
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $photoFile = $form->get('photo')->getData();

            // Si un fichier a été uploadé
            if ($photoFile) {
                // On le renomme
                $newFilename = uniqid().'.'.$photoFile->guessExtension();

                // On essaie de le déplacer sur le serveur
                try {
                    $photoFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible d\'uploader la photo');
                }

                // On met àjour l'objet avec le bon nom de fichier
                $athlete->setPhoto($newFilename);
            }
            $em->persist($athlete); // prépare la sauvegarde
            $em->flush(); // execute la sauvegarde

            $this->addFlash('success', 'Athlete ajouté');
        }

        // Récupération d'une table (athlete)
        $athletes = $em->getRepository(Athlete::class)->findAll();

        return $this->render('athlete/index.html.twig', [
            'athletes' => $athletes,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/athlete/edit/{id}", name="edit_athlete")
     */
    public function edit(Athlete $athlete = null, Request $request){
        if($athlete == null){
            $this->addFlash('danger', 'Athlete introuvable');
            return $this->redirectToRoute('athlete');
        }

        $form = $this->createForm(Athlete::class, $athlete);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $photoFile = $form->get('photo')->getData();

            // Si un fichier a été uploadé
            if ($photoFile) {
                // On le renomme
                $newFilename = uniqid().'.'.$photoFile->guessExtension();

                // On essaie de le déplacer sur le serveur
                try {
                    $photoFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible d\'uploader la photo');
                }

                // On met àjour l'objet avec le bon nom de fichier
                $athlete->setPhoto($newFilename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($athlete);
            $em->flush();

            $this->addFlash('success', 'Athlete mise à jour');
        }

        return $this->render('athlete/edit.html.twig', [
            'athlete' => $athlete,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/athlete/delete/{id}", name="delete_athlete")
     */
    public function delete(Athlete $athlete = null){
        if($athlete == null){
            $this->addFlash('danger', 'Athlete introuvable');
            return $this->redirectToRoute('athlete');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($athlete);
        $em->flush();

        $this->addFlash('warning', 'Athlete supprimé');

        return $this->redirectToRoute('athlete');
    }
}
