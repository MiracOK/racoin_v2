<?php
/**
 * Created by PhpStorm.
 * User: ponicorn
 * Date: 26/01/15
 * Time: 00:25
 */

namespace controller;
use model\Annonce;
use model\Annonceur;
use model\Photo;

class viewAnnonceur {
    public function __construct(){
    }
    function afficherAnnonceur($twig, $menu, $chemin, $n, $cat) {
        $this->annonceur = annonceur::find($n);
        if(!isset($this->annonceur)){
            echo "404";
            return;
        }
        $resultAnnonce = annonce::where('id_annonceur','=',$n)->get();

        $annonces = [];
        foreach ($resultAnnonce as $annonce) {
            $annonce->nb_photo = Photo::where('id_annonce', '=', $annonce->id_annonce)->count();
            if($annonce->nb_photo>0){
                $annonce->url_photo = Photo::select('url_photo')
                    ->where('id_annonce', '=', $annonce->id_annonce)
                    ->first()->url_photo;
            }else{
                $annonce->url_photo = $chemin.'/img/noimg.png';
            }

            $annonces[] = $annonce;
        }
        $template = $twig->load("annonceur.html.twig");
        echo $template->render(array('nom' => $this->annonceur,
            "chemin" => $chemin,
            "annonces" => $annonces,
            "categories" => $cat));
    }
}
