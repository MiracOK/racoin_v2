<?php

namespace controller;

use AllowDynamicProperties;
use model\Annonce;
use model\Annonceur;
use model\Departement;
use model\Photo;
use model\Categorie;

#[AllowDynamicProperties] class item
{
    public function __construct() {}
    function afficherItem($twig, $menu, $chemin, $n, $cat): void
    {

        $this->annonce = Annonce::find($n);
        if (!isset($this->annonce)) {
            echo "404";
            return;
        }

        $menu = array(
            array(
                'href' => $chemin,
                'text' => 'Acceuil'
            ),
            array(
                'href' => $chemin . "/cat/" . $n,
                'text' => Categorie::find($this->annonce->id_categorie)?->nom_categorie
            ),
            array(
                'href' => $chemin . "/item/" . $n,
                'text' => $this->annonce->titre
            )
        );

        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->departement = Departement::find($this->annonce->id_departement);
        $this->photo = Photo::where('id_annonce', '=', $n)->get();
        $template = $twig->load("item.html.twig");
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "annonceur" => $this->annonceur,
            "dep" => $this->departement->nom_departement,
            "photo" => $this->photo,
            "categories" => $cat
        ));
    }

    function supprimerItemGet($twig, $menu, $chemin, $n)
    {
        $this->annonce = Annonce::find($n);
        if (!isset($this->annonce)) {
            echo "404";
            return;
        }
        $template = $twig->load("delGet.html.twig");
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce
        ));
    }


    function supprimerItemPost($twig, $menu, $chemin, $n, $cat)
    {
        $this->annonce = Annonce::find($n);
        $reponse = false;
        if (password_verify($_POST["pass"], $this->annonce->mdp)) {
            $reponse = true;
            photo::where('id_annonce', '=', $n)->delete();
            $this->annonce->delete();
        }

        $template = $twig->load("delPost.html.twig");
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "pass" => $reponse,
            "categories" => $cat
        ));
    }

    function modifyGet($twig, $menu, $chemin, $id)
    {
        $this->annonce = Annonce::find($id);
        if (!isset($this->annonce)) {
            echo "404";
            return;
        }
        $template = $twig->load("modifyGet.html.twig");
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce
        ));
    }

    function modifyPost($twig, $menu, $chemin, $n, $cat, $dpt)
    {
        $this->annonce = Annonce::find($n);
        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->categItem = Categorie::find($this->annonce->id_categorie)->nom_categorie;
        $this->dptItem = Departement::find($this->annonce->id_departement)->nom_departement;

        $reponse = false;
        if (password_verify($_POST["pass"], $this->annonce->mdp)) {
            $reponse = true;
        }

        $template = $twig->load("modifyPost.html.twig");
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "annonceur" => $this->annonceur,
            "pass" => $reponse,
            "categories" => $cat,
            "departements" => $dpt,
            "dptItem" => $this->dptItem,
            "categItem" => $this->categItem
        ));
    }

    function edit($twig, $menu, $chemin, $allPostVars, $id)
    {

        date_default_timezone_set('Europe/Paris');

        /*
        * On récupère tous les champs du formulaire en supprimant
        * les caractères invisibles en début et fin de chaîne.
        */
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $ville = trim($_POST['ville']);
        $departement = trim($_POST['departement']);
        $categorie = trim($_POST['categorie']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);


        // Tableau d'erreurs personnalisées
        $errors = array();
        $errors['nameAdvertiser'] = '';
        $errors['emailAdvertiser'] = '';
        $errors['phoneAdvertiser'] = '';
        $errors['villeAdvertiser'] = '';
        $errors['departmentAdvertiser'] = '';
        $errors['categorieAdvertiser'] = '';
        $errors['titleAdvertiser'] = '';
        $errors['descriptionAdvertiser'] = '';
        $errors['priceAdvertiser'] = '';


        // On teste que les champs ne soient pas vides et soient de bons types
        if (empty($nom)) {
            $errors['nameAdvertiser'] = 'Veuillez entrer votre nom';
        }
        if (!$this->isEmail($email)) {
            $errors['emailAdvertiser'] = 'Veuillez entrer une adresse mail correcte';
        }
        if (empty($phone) && !is_numeric($phone)) {
            $errors['phoneAdvertiser'] = 'Veuillez entrer votre numéro de téléphone';
        }
        if (empty($ville)) {
            $errors['villeAdvertiser'] = 'Veuillez entrer votre ville';
        }
        if (!is_numeric($departement)) {
            $errors['departmentAdvertiser'] = 'Veuillez choisir un département';
        }
        if (!is_numeric($categorie)) {
            $errors['categorieAdvertiser'] = 'Veuillez choisir une catégorie';
        }
        if (empty($title)) {
            $errors['titleAdvertiser'] = 'Veuillez entrer un titre';
        }
        if (empty($description)) {
            $errors['descriptionAdvertiser'] = 'Veuillez entrer une description';
        }
        if (empty($price) || !is_numeric($price)) {
            $errors['priceAdvertiser'] = 'Veuillez entrer un prix';
        }

        // On vire les cases vides
        $errors = array_values(array_filter($errors));

        // S'il y a des erreurs on redirige vers la page d'erreur
        if (!empty($errors)) {

            $template = $twig->load("add-error.html.twig");
            echo $template->render(
                array(
                    "breadcrumb" => $menu,
                    "chemin" => $chemin,
                    "errors" => $errors
                )
            );
        }
        // sinon on ajoute à la base et on redirige vers une page de succès
        else {
            // 1. Récupération des modèles
            $annonce = Annonce::find($id);
            $annonceur = Annonceur::find($annonce->id_annonceur);

            // 2. Mise à jour de l'annonceur
            $annonceur->update([
                'email' => htmlentities($allPostVars['email']),
                'nom_annonceur' => htmlentities($allPostVars['nom']),
                'telephone' => htmlentities($allPostVars['phone'])
            ]);

            // 3. Mise à jour de l'annonce
            // Note: mot de passe mis à jour uniquement s'il n'est pas vide (bonne pratique souvent oubliée)
            $annonceData = [
                'ville' => htmlentities($allPostVars['ville']),
                'id_departement' => $allPostVars['departement'],
                'prix' => htmlentities($allPostVars['price']),
                'titre' => htmlentities($allPostVars['title']),
                'description' => htmlentities($allPostVars['description']),
                'id_categorie' => $allPostVars['categorie'],
                'date' => date('Y-m-d')
            ];

            // Si on a fourni un nouveau mot de passe, on l'ajoute au tableau
            if (!empty($allPostVars['psw'])) {
                $annonceData['mdp'] = password_hash($allPostVars['psw'], PASSWORD_DEFAULT);
            }

            $annonce->update($annonceData);

            // 4. Affichage du succès
            $template = $twig->load("modif-confirm.html.twig");
            echo $template->render(array("breadcrumb" => $menu, "chemin" => $chemin));
        }
    }
    function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
