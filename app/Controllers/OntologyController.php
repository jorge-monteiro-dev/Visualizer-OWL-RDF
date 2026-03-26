<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\OntologyParser;

class OntologyController extends Controller
{
    private const ALLOWED_EXTENSIONS = ['owl', 'rdf', 'xml', 'jsonld', 'json'];
    private const MAX_FILE_SIZE      = 10 * 1024 * 1024; // équivalent à 10 MB

    public function upload(Request $request): void
    {
        $file = $request->file('ontology');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'Fichier invalide ou erreur de téléversement.'], 400);
            return;
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->json(['error' => 'Fichier trop volumineux (max 10 Mo).'], 400);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            $this->json(['error' => 'Format non supporté. Extensions acceptées : .owl, .rdf, .xml, .jsonld'], 400);
            return;
        }

        $filename = str_replace('.', '_', uniqid('onto_', true)) . '.' . $ext;
        $destPath = UPLOAD_PATH . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->json(['error' => 'Erreur lors de la sauvegarde du fichier.'], 500);
            return;
        }

        try {
            $parser = new OntologyParser();
            $graph  = $parser->parseFile($destPath);

            $cacheFile = UPLOAD_PATH . '/' . pathinfo($filename, PATHINFO_FILENAME) . '_graph.json';
            file_put_contents($cacheFile, json_encode($graph, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

            $this->json([
                'success'   => true,
                'token'     => pathinfo($filename, PATHINFO_FILENAME),
                'filename'  => $file['name'],
                'stats'     => $graph['stats'],
                'redirect'  => '/visualize?token=' . pathinfo($filename, PATHINFO_FILENAME),
            ]);
        } catch (\Throwable $e) {
            @unlink($destPath);
            $this->json(['error' => 'Erreur de parsing : ' . $e->getMessage()], 422);
        }
    }

    public function visualize(Request $request): void
    {
        $token    = $request->get('token', '');
        $filename = $request->get('filename', 'Ontologie');

        $token = preg_replace('/[^a-zA-Z0-9_\-]/', '', $token);

        $this->view('visualize', [
            'title'    => 'OntoViz — ' . htmlspecialchars($filename),
            'token'    => $token,
            'filename' => htmlspecialchars($filename),
        ]);
    }

    public function graphData(Request $request): void
    {
        $token = $request->get('token', '');
        $token = preg_replace('/[^a-zA-Z0-9_\-]/', '', $token);

        if (empty($token)) {
            $this->json(['error' => 'Token manquant.'], 400);
            return;
        }

        $cacheFile = UPLOAD_PATH . '/' . $token . '_graph.json';

        if (!file_exists($cacheFile)) {
            $this->json(['error' => 'Graphe introuvable. Veuillez re-téléverser le fichier.'], 404);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        readfile($cacheFile);
    }

    public function demoData(Request $request): void
    {
        $demo = $this->generateDemoOntology();
        $this->json($demo);
    }

    public function parseInline(Request $request): void
    {
        $body = $request->body();
        $data = json_decode($body, true);

        $content  = $data['content'] ?? '';
        $filename = $data['filename'] ?? 'inline.owl';

        if (empty($content)) {
            $this->json(['error' => 'Contenu vide.'], 400);
            return;
        }

        try {
            $parser = new OntologyParser();
            $graph  = $parser->parseContent($content, $filename);
            $this->json($graph);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }


    private function generateDemoOntology(): array
    {
        $nodes = [
            ['id' => 0,  'uri' => 'owl:Thing',                 'label' => 'Thing',                  'type' => 'class',           'comment' => 'Classe racine OWL'],
            ['id' => 1,  'uri' => '#Food',                     'label' => 'Food',                   'type' => 'class',           'comment' => 'Nourriture'],
            ['id' => 2,  'uri' => '#Pizza',                    'label' => 'Pizza',                  'type' => 'class',           'comment' => 'Une pizza'],
            ['id' => 3,  'uri' => '#PizzaBase',                'label' => 'PizzaBase',              'type' => 'class',           'comment' => 'Base de pizza'],
            ['id' => 4,  'uri' => '#PizzaTopping',             'label' => 'PizzaTopping',           'type' => 'class',           'comment' => 'Garniture'],
            ['id' => 5,  'uri' => '#NamedPizza',               'label' => 'NamedPizza',             'type' => 'class',           'comment' => 'Pizza nommée'],
            ['id' => 6,  'uri' => '#MargheritaPizza',          'label' => 'Margherita',             'type' => 'class',           'comment' => 'Pizza Margherita'],
            ['id' => 7,  'uri' => '#AmericanaPizza',           'label' => 'Americana',              'type' => 'class',           'comment' => 'Pizza Americana'],
            ['id' => 8,  'uri' => '#VegetarianPizza',          'label' => 'VegetarianPizza',        'type' => 'class',           'comment' => 'Pizza végétarienne'],
            ['id' => 9,  'uri' => '#CheeseTopping',            'label' => 'CheeseTopping',          'type' => 'class',           'comment' => 'Fromage'],
            ['id' => 10, 'uri' => '#MozzarellaTopping',        'label' => 'Mozzarella',             'type' => 'class',           'comment' => 'Mozzarella'],
            ['id' => 11, 'uri' => '#TomatoTopping',            'label' => 'TomatoTopping',          'type' => 'class',           'comment' => 'Tomate'],
            ['id' => 12, 'uri' => '#MeatTopping',              'label' => 'MeatTopping',            'type' => 'class',           'comment' => 'Viande'],
            ['id' => 13, 'uri' => '#PepperoniTopping',         'label' => 'Pepperoni',              'type' => 'class',           'comment' => 'Pepperoni'],
            ['id' => 14, 'uri' => '#VegetableTopping',         'label' => 'VegetableTopping',       'type' => 'class',           'comment' => 'Légume'],
            ['id' => 15, 'uri' => '#MushroomTopping',          'label' => 'Mushroom',               'type' => 'class',           'comment' => 'Champignon'],
            ['id' => 16, 'uri' => '#SpicyPizza',               'label' => 'SpicyPizza',             'type' => 'class',           'comment' => 'Pizza épicée'],
            ['id' => 17, 'uri' => '#hasTopping',               'label' => 'hasTopping',             'type' => 'objectProperty',  'comment' => 'A une garniture'],
            ['id' => 18, 'uri' => '#hasBase',                  'label' => 'hasBase',                'type' => 'objectProperty',  'comment' => 'A une base'],
            ['id' => 19, 'uri' => '#isIngredientOf',           'label' => 'isIngredientOf',         'type' => 'objectProperty',  'comment' => 'Est ingrédient de'],
            ['id' => 20, 'uri' => '#hasSpiciness',             'label' => 'hasSpiciness',           'type' => 'datatypeProperty','comment' => 'Niveau de piquant'],
            ['id' => 21, 'uri' => '#ThinAndCrispyBase',        'label' => 'ThinAndCrispy',          'type' => 'class',           'comment' => 'Base fine et croustillante'],
            ['id' => 22, 'uri' => '#DeepPanBase',              'label' => 'DeepPan',                'type' => 'class',           'comment' => 'Base épaisse'],
            ['id' => 23, 'uri' => '#GarlicTopping',            'label' => 'Garlic',                 'type' => 'class',           'comment' => 'Ail'],
            ['id' => 24, 'uri' => '#OliveTopping',             'label' => 'Olive',                  'type' => 'class',           'comment' => 'Olive'],
        ];

        foreach ($nodes as &$n) {
            $n['rdfType']    = '';
            $n['properties'] = [];
        }

        $edges = [
            ['source' => 1,  'target' => 0,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 2,  'target' => 1,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 3,  'target' => 1,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 4,  'target' => 1,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 5,  'target' => 2,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 8,  'target' => 5,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 16, 'target' => 5,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 6,  'target' => 8,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 7,  'target' => 5,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 9,  'target' => 4,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 10, 'target' => 9,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 11, 'target' => 4,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 12, 'target' => 4,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 13, 'target' => 12, 'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 14, 'target' => 4,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 15, 'target' => 14, 'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 23, 'target' => 14, 'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 24, 'target' => 14, 'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 21, 'target' => 3,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 22, 'target' => 3,  'predicate' => 'rdfs:subClassOf', 'label' => 'subClassOf',   'type' => 'subClassOf'],
            ['source' => 17, 'target' => 2,  'predicate' => 'rdfs:domain',     'label' => 'domain',       'type' => 'domain'],
            ['source' => 17, 'target' => 4,  'predicate' => 'rdfs:range',      'label' => 'range',        'type' => 'range'],
            ['source' => 18, 'target' => 2,  'predicate' => 'rdfs:domain',     'label' => 'domain',       'type' => 'domain'],
            ['source' => 18, 'target' => 3,  'predicate' => 'rdfs:range',      'label' => 'range',        'type' => 'range'],
            ['source' => 19, 'target' => 17, 'predicate' => 'owl:inverseOf',   'label' => 'inverseOf',    'type' => 'relation'],
            ['source' => 20, 'target' => 2,  'predicate' => 'rdfs:domain',     'label' => 'domain',       'type' => 'domain'],
            ['source' => 6,  'target' => 10, 'predicate' => '#hasTopping',     'label' => 'hasTopping',   'type' => 'relation'],
            ['source' => 6,  'target' => 11, 'predicate' => '#hasTopping',     'label' => 'hasTopping',   'type' => 'relation'],
            ['source' => 7,  'target' => 13, 'predicate' => '#hasTopping',     'label' => 'hasTopping',   'type' => 'relation'],
            ['source' => 7,  'target' => 11, 'predicate' => '#hasTopping',     'label' => 'hasTopping',   'type' => 'relation'],
        ];

        $stats = [
            'totalNodes'    => count($nodes),
            'totalEdges'    => count($edges),
            'nodeTypes'     => [
                'class'            => 20,
                'objectProperty'   => 3,
                'datatypeProperty' => 1,
            ],
            'edgeTypes'     => [
                'subClassOf' => 19,
                'domain'     => 3,
                'range'      => 2,
                'relation'   => 6,
            ],
            'namespaceCount' => 3,
        ];

        return compact('nodes', 'edges', 'stats');
    }
}
