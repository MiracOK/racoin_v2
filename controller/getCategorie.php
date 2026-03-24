<?php

namespace controller;

use model\Categorie;
use model\Annonce;
use model\Photo;
use model\Annonceur;

class getCategorie {

    protected $categories = array();

    public function getCategories() {
        return Categorie::orderBy('nom_categorie')->get()->toArray();
    }

    public function getCategorieContent($chemin, $id) {
        $annonceResutl = Annonce::with("Annonceur")->orderBy('id_annonce','desc')->where('id_categorie', "=", $id)->get();
        $annonce = [];
        foreach($annonceResutl as $result) {
            $result->nb_photo = Photo::where("id_annonce", "=", $result->id_annonce)->count();
            $result->url_photo = $chemin.'/img/noimg.png';
            if($result->nb_photo > 0){
                $result->url_photo = Photo::select("url_photo")
                    ->where("id_annonce", "=", $result->id_annonce)
                    ->first()->url_photo;
            }
            $result->nom_annonceur = Annonceur::select("nom_annonceur")
                ->where("id_annonceur", "=", $result->id_annonceur)
                ->first()->nom_annonceur;
            array_push($annonce, $result);
        }
        $this->annonce = $annonce;
    }

    public function displayCategorie($twig, $menu, $chemin, $cat, $id) {
        $template = $twig->load("index.html.twig");
        $menu = array(
            array('href' => $chemin,
                'text' => 'Acceuil'),
            array('href' => $chemin."/cat/".$id,
                'text' => Categorie::find($id)->nom_categorie)
        );

        $this->getCategorieContent($chemin, $id);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "categories" => $cat,
            "annonces" => $this->annonce));
    }
}
