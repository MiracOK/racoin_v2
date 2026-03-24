<?php

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as Capsule;
use controller\getCategorie;
use model\Categorie;

class GetCategorieTest extends TestCase
{
    /**
     * S'exécute avant chaque test. 
     * Initialise une BDD en mémoire pour simuler la base.
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

        Capsule::schema()->create('categorie', function ($table) {
            $table->increments('id_categorie');
            $table->string('nom_categorie');
        });

        Capsule::schema()->create('annonce', function ($table) {
            $table->increments('id_annonce');
            $table->integer('id_categorie');
            $table->integer('id_annonceur')->nullable();
            $table->string('titre')->nullable();
        });

        Capsule::schema()->create('photo', function ($table) {
            $table->increments('id_photo');
            $table->integer('id_annonce');
            $table->string('url_photo');
        });

        Capsule::schema()->create('annonceur', function ($table) {
            $table->increments('id_annonceur');
            $table->string('nom_annonceur');
        });

        Categorie::insert([
            'id_categorie' => 1,
            'nom_categorie' => 'Voitures'
        ]);
    }

    public function testDisplayCategorieRendersExpectedTwigTemplate()
    {
        $controller = new getCategorie();
        
        // Mock de l'environnement Twig
        $twigMock = $this->createMock(\Twig\Environment::class);
        
        // Mock de Twig Template pour simuler le Wrapper
        $templateMock = $this->getMockBuilder(\Twig\Template::class)
                           ->disableOriginalConstructor()
                           ->onlyMethods(['render'])
                           ->getMockForAbstractClass();
                           
        $templateWrapper = new \Twig\TemplateWrapper($twigMock, $templateMock);

        $twigMock->expects($this->once())
                 ->method('load')
                 ->with('index.html.twig')
                 ->willReturn($templateWrapper);

        $templateMock->expects($this->once())
                     ->method('render')
                     ->willReturn('HTML Rendu par Twig');

        ob_start();
        try {
            $controller->displayCategorie($twigMock, [], '/chemin/test', ['cat1'], 1);
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $this->assertEquals('HTML Rendu par Twig', $output);
    }
}