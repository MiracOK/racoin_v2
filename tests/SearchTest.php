<?php

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as Capsule;
use controller\Search;
use model\Annonce;
use model\Categorie;

class SearchTest extends TestCase
{
    /**
     * S'exécute avant chaque test.
     * On prépare une base de données en mémoire avec quelques annonces de test.
     */
    protected function setUp(): void
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // 1. Création des tables "categorie" et "annonce"
        Capsule::schema()->create('categorie', function ($table) {
            $table->increments('id_categorie');
            $table->string('nom_categorie');
        });

        Capsule::schema()->create('annonce', function ($table) {
            $table->increments('id_annonce');
            $table->integer('id_categorie');
            $table->string('description')->nullable();
            $table->string('ville')->nullable();
            $table->integer('prix')->nullable();
        });

        // 2. Insertion de fausses catégories
        Categorie::insert([
            ['id_categorie' => 1, 'nom_categorie' => 'Véhicules'],
            ['id_categorie' => 2, 'nom_categorie' => 'Maison']
        ]);

        // 3. Insertion de fausses annonces
        Annonce::insert([
            ['id_annonce' => 1, 'id_categorie' => 1, 'description' => 'Belle voiture rouge', 'ville' => '75000', 'prix' => 5000],
            ['id_annonce' => 2, 'id_categorie' => 2, 'description' => 'Canapé en cuir', 'ville' => '69000', 'prix' => 150]
        ]);
    }

    /**
     * Teste l'affichage de la page de base.
     */
    public function testShowAfficheLaPageDeRecherche()
    {
        $controller = new Search();
        
        // Mock de l'environnement Twig
        $twigMock = $this->createMock(\Twig\Environment::class);
        $templateMock = $this->getMockBuilder(\Twig\Template::class)
                             ->disableOriginalConstructor()
                             ->onlyMethods(['render'])
                             ->getMockForAbstractClass();
                             
        $templateWrapper = new \Twig\TemplateWrapper($twigMock, $templateMock);

        // On vérifie qu'il charge bien le fichier search.html.twig
        $twigMock->expects($this->once())
                 ->method('load')
                 ->with('search.html.twig')
                 ->willReturn($templateWrapper);

        $templateMock->expects($this->once())
                     ->method('render')
                     ->willReturn('HTML Recherche');

        ob_start();
        $controller->show($twigMock, [], '/chemin', []);
        $output = ob_get_clean();

        $this->assertEquals('HTML Recherche', $output);
    }

    /**
     * Teste que la recherche avec le mot "voiture" trouve bien 1 résultat
     * et ignore le canapé.
     */
    public function testResearchTrouveAnnonceParMotCle()
    {
        $controller = new Search();
        
        // On simule le tableau que le formulaire web enverrait
        $parametresPost = [
            'motclef'    => 'voiture',
            'codepostal' => '',
            'categorie'  => 'Toutes catégories',
            'prix-min'   => 'Min',
            'prix-max'   => 'Max'
        ];

        // Mock de Twig
        $twigMock = $this->createMock(\Twig\Environment::class);
        $templateMock = $this->getMockBuilder(\Twig\Template::class)
                             ->disableOriginalConstructor()
                             ->onlyMethods(['render'])
                             ->getMockForAbstractClass();
                             
        $templateWrapper = new \Twig\TemplateWrapper($twigMock, $templateMock);

        $twigMock->expects($this->once())
                 ->method('load')
                 ->with('index.html.twig')
                 ->willReturn($templateWrapper);

        // Ici on va vérifier si Twig reçoit bien 1 seule annonce (la voiture)
        $templateMock->expects($this->once())
                     ->method('render')
                     ->willReturnCallback(function($variables) {
                         // Vérifie qu'on a bien trouvé 1 seule annonce
                         $this->assertCount(1, $variables['annonces']);
                         // Vérifie que c'est bien l'annonce attendue
                         $this->assertStringContainsString('voiture', $variables['annonces'][0]->description);
                         return 'HTML Resultat';
                     });

        ob_start();
        $controller->research($parametresPost, $twigMock, [], '/chemin', []);
        $output = ob_get_clean();

        $this->assertEquals('HTML Resultat', $output);
    }
}