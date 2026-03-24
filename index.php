<?php
require 'vendor/autoload.php';

use controller\getCategorie;
use controller\getDepartment;
use controller\index;
use controller\item;
use db\connection;

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use model\Annonce;
use model\Categorie;
use model\Annonceur;
use model\Departement;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

connection::createConn();

// Initialisation de Slim 4
$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Initialisation de Twig
$loader = new FilesystemLoader(__DIR__ . '/template');
$twig   = new Environment($loader);

// Ajout d'un middleware pour le trailing slash
$app->add(function (Request $request, $handler) {
    $uri  = $request->getUri();
    $path = $uri->getPath();
    
    if ($path != '/' && str_ends_with($path, '/')) {
        $uri = $uri->withPath(substr($path, 0, -1));
        if ($request->getMethod() == 'GET') {
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', (string)$uri)->withStatus(301);
        } else {
            return $handler->handle($request->withUri($uri));
        }
    }
    return $handler->handle($request);
});


if (!isset($_SESSION)) {
    session_start();
    $_SESSION['formStarted'] = true;
}

if (!isset($_SESSION['token'])) {
    $token                  = md5(uniqid(rand(), TRUE));
    $_SESSION['token']      = $token;
    $_SESSION['token_time'] = time();
} else {
    $token = $_SESSION['token'];
}

$menu = [
    [
        'href' => './index.php',
        'text' => 'Accueil'
    ]
];

$chemin = dirname($_SERVER['SCRIPT_NAME']);

$cat = new getCategorie();
$dpt = new getDepartment();

$app->get('/', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
    $index = new index();
    ob_start();
    $index->displayAllAnnonce($twig, $menu, $chemin, $cat->getCategories());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->get('/item/{id}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat) {
    $id     = $arg['id'];
    $item = new item();
    ob_start();
    $item->afficherItem($twig, $menu, $chemin, $id, $cat->getCategories());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->get('/add', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat, $dpt) {
    $ajout = new controller\addItem();
    ob_start();
    $ajout->addItemView($twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->post('/add', function (Request $request, Response $response) use ($twig, $menu, $chemin) {
    $allPostVars = (array)$request->getParsedBody();
    $ajout       = new controller\addItem();
    ob_start();
    $ajout->addNewItem($twig, $menu, $chemin, $allPostVars);
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->get('/item/{id}/edit', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin) {
    $id   = $arg['id'];
    $item = new item();
    ob_start();
    $item->modifyGet($twig, $menu, $chemin, $id);
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->post('/item/{id}/edit', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat, $dpt) {
    $id          = $arg['id'];
    $allPostVars = (array)$request->getParsedBody();
    $item        = new item();
    ob_start();
    $item->modifyPost($twig, $menu, $chemin, $id, $allPostVars, $cat->getCategories(), $dpt->getAllDepartments());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

// Changed from map(['GET, POST']) to any() or explicit mapping?
$app->map(['GET', 'POST'], '/item/{id}/confirm', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin) {
    $id   = $arg['id'];
    $allPostVars = (array)$request->getParsedBody();
    $item        = new item();
    ob_start();
    $item->edit($twig, $menu, $chemin, $allPostVars, $id);
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->get('/search', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
    $s = new controller\Search();
    ob_start();
    $s->show($twig, $menu, $chemin, $cat->getCategories());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->post('/search', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
    $array = (array)$request->getParsedBody();
    $s     = new controller\Search();
    ob_start();
    $s->research($array, $twig, $menu, $chemin, $cat->getCategories());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->get('/annonceur/{id}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat) {
    $id         = $arg['id'];
    $annonceur  = new controller\viewAnnonceur();
    ob_start();
    $annonceur->afficherAnnonceur($twig, $menu, $chemin, $id, $cat->getCategories());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->get('/del/{id}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin) {
    $id    = $arg['id'];
    $item  = new controller\item();
    ob_start();
    $item->supprimerItemGet($twig, $menu, $chemin, $id);
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->post('/del/{id}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat) {
    $id    = $arg['id'];
    $item = new controller\item();
    ob_start();
    $item->supprimerItemPost($twig, $menu, $chemin, $id, $cat->getCategories());
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

$app->get('/cat/{id}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat) {
    $id = $arg['id'];
    $categorie = new controller\getCategorie();
    ob_start();
    $categorie->displayCategorie($twig, $menu, $chemin, $cat->getCategories(), $id);
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

// Remove $app->run() from here as we will put it at the very bottom

$app->get('/api(/)', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
    $template = $twig->load('api.html.twig');
    $menu     = array(
        array(
            'href' => $chemin,
            'text' => 'Acceuil'
        ),
        array(
            'href' => $chemin . '/api',
            'text' => 'Api'
        )
    );
    ob_start();
    echo $template->render(array('breadcrumb' => $menu, 'chemin' => $chemin));
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $group) use ($twig, $menu, $chemin, $cat) {

    $group->group('/annonce', function (RouteCollectorProxy $group) {

        $group->get('/{id}', function (Request $request, Response $response, array $arg) {
            $id          = $arg['id'];
            $annonceList = ['id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur', 'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'];
            $return      = Annonce::select($annonceList)->find($id);

            if (isset($return)) {
                $return->categorie     = Categorie::find($return->categorie);
                $return->annonceur     = Annonceur::select('email', 'nom_annonceur', 'telephone')
                    ->find($return->annonceur);
                $return->departement   = Departement::select('id_departement', 'nom_departement')->find($return->departement);
                $links                 = [];
                $links['self']['href'] = '/api/annonce/' . $return->id_annonce;
                $return->links         = $links;
                $response->getBody()->write($return->toJson());
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                throw new \Slim\Exception\HttpNotFoundException($request);
            }
        });
    });

    $group->group('/annonces(/)', function (RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            $annonceList = ['id_annonce', 'prix', 'titre', 'ville'];
            $annonces     = Annonce::all($annonceList);
            $links = [];
            foreach ($annonces as $annonce) {
                $links['self']['href'] = '/api/annonce/' . $annonce->id_annonce;
                $annonce->links            = $links;
            }
            $links['self']['href'] = '/api/annonces/';
            $annonces->links              = $links;
            $response->getBody()->write($annonces->toJson());
            return $response->withHeader('Content-Type', 'application/json');
        });
    });


    $group->group('/categorie', function (RouteCollectorProxy $group) {

        $group->get('/{id}', function (Request $request, Response $response, array $arg) {
            $id = $arg['id'];
            $annonces     = Annonce::select('id_annonce', 'prix', 'titre', 'ville')
                ->where('id_categorie', '=', $id)
                ->get();
            $links = [];

            foreach ($annonces as $annonce) {
                $links['self']['href'] = '/api/annonce/' . $annonce->id_annonce;
                $annonce->links            = $links;
            }

            $category                     = Categorie::find($id);
            $links['self']['href'] = '/api/categorie/' . $id;
            $category->links              = $links;
            $category->annonces           = $annonces;
            echo $category->toJson();
        });
    });

    $app->group('/categories(/)', function () use ($app) {
        $app->get('/', function ($request, $response, $arg) use ($app) {
            $response->headers->set('Content-Type', 'application/json');
            $category     = Categorie::get();
            $links = [];
            foreach ($category as $cat) {
                $links['self']['href'] = '/api/categorie/' . $cat->id_categorie;
                $cat->links            = $links;
            }
            $links['self']['href'] = '/api/categories/';
            $category->links              = $links;
            echo $category->toJson();
        });
    });

    $app->get('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $kg = new controller\KeyGenerator();
        $kg->show($twig, $menu, $chemin, $cat->getCategories());
    });

    $app->post('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $nom = $_POST['nom'];

        $kg = new controller\KeyGenerator();
        $kg->generateKey($twig, $menu, $chemin, $cat->getCategories(), $nom);
    });
});


$app->run();
