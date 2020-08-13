<?php

namespace App\Controller;


use App\Entity\Pays;
use App\Form\PaysType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PaysController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        // Récupération de Doctrine (connexion à la bdd)
        $em = $this->getDoctrine()->getManager();

        $pays = new Pays();
        $form = $this->createForm(PaysType::class, $pays);

        // Analyse la requete
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $photoFile = $form->get('drapeau')->getData();

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
                    $this->addFlash('danger', 'Impossible d\'uploader le drapeau');
                }

                // On met àjour l'objet avec le bon nom de fichier
                $pays->setDrapeau($newFilename);
            }
            $em->persist($pays); // prépare la sauvegarde
            $em->flush(); // execute la sauvegarde

            $this->addFlash('success', 'Pays ajouté');
        }

        // Récupération d'une table (pays)
        $payss = $em->getRepository(Pays::class)->findAll();

        return $this->render('pays/index.html.twig', [
            'payss' => $payss,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_pays")
     */
    public function edit(Pays $pays = null, Request $request){
        if($pays == null){
            $this->addFlash('danger', 'Pays introuvable');
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $photoFile = $form->get('drapeau')->getData();

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
                $pays->setDrapeau($newFilename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($pays);
            $em->flush();

            $this->addFlash('success', 'Pays mise à jour');
        }
        
        return $this->render('pays/edit.html.twig', [
            'pays' => $pays,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete_pays")
     */
    public function delete(Pays $pays = null){
        if($pays == null){
            $this->addFlash('danger', 'Pays introuvable');
            return $this->redirectToRoute('home');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($pays);
        $em->flush();

        $this->addFlash('warning', 'Pays supprimé');

        return $this->redirectToRoute('home');
    }
}
