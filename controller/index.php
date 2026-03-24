<?php

namespace controller;

use model\Annonce;
use model\Photo;
use model\Annonceur;

class index
{
    protected $annonce = array();

    public function displayAllAnnonce($twig, $menu, $chemin, $cat)
    {
        $template = $twig->load("index.html.twig");
        $menu     = array(
            array(
                'href' => $chemin,
                'text' => 'Acceuil'
            ),
        );

        $this->getAll($chemin);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $this->annonce
        ));
    }

    public function getAll()
    {
        $annonceResult     = Annonce::with("Annonceur")->orderBy('id_annonce', 'desc')->take(12)->get();
        $annonce = [];
        foreach ($annonceResult as $result) {
            $result->nb_photo = Photo::where("id_annonce", "=", $result->id_annonce)->count();
            if ($result->nb_photo > 0) {
                $result->url_photo = Photo::select("url_photo")
                    ->where("id_annonce", "=", $result->id_annonce)
                    ->first()->url_photo;
            } else {
                $result->url_photo = '/img/noimg.png';
            }
            $result->nom_annonceur = Annonceur::select("nom_annonceur")
                ->where("id_annonceur", "=", $result->id_annonceur)
                ->first()->nom_annonceur;
            array_push($annonce, $result);
        }
        $this->annonce = $annonce;
    }
}
