# 🌀 Visualizer — Visualiseur OWL/RDF

Application PHP 8.5 MVC pour visualiser des ontologies sémantiques (OWL, RDF) avec D3.js v7.

---

- **Upload de fichiers OWL, RDF/XML** — parsing côté serveur en PHP pur
- **3 vues D3.js interactives** :
  - **Force** — graphe force-directed avec détection de clustering, flèches directionnelles, drag & drop
  - **Radiale(Sunburst)** — partition radiale en couches par profondeur hiérarchique
  - **Hiérarchie** — arbre collapsible horizontal (cliquer sur un nœud pour déplier/replier)
- **Panneau de détail** — affiche URI, type, commentaire, propriétés, et voisins du nœud sélectionné
- **Filtres dynamiques** par type de nœud (Classe, Propriété objet, Propriété de données, Individu…)
- **Recherche** de nœuds par nom ou URI avec surbrillance
- **Zoom/Pan/Export SVG** depuis la barre de contrôles
- **Démo intégrée** — Pizza Ontology sans fichier requis
- **Fichiers d'exemples** : `examples/animals.owl`, `examples/foaf.ttl`

---

## Installation & Démarrage

### Prérequis

- PHP ≥ 8.3 (testé 8.3 ; compatible 8.5)
- Composer ≥ 2.7
- Extensions PHP : `simplexml`, `mbstring`, `libxml` (incluses par défaut)

### 1. Cloner / extraire le projet

```bash
cd /path/to/project
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Lancer le serveur de développement

```bash
php -S localhost:8080 -t public router.php
```

Ouvrir : **http://localhost:8080**

---

## Déploiement Apache/Nginx

### Apache

Pointer le DocumentRoot vers `public/`. Le fichier `public/.htaccess` gère la réécriture d'URL :

```apache
<VirtualHost *:80>
    ServerName ontoviz.local
    DocumentRoot /var/www/ontoviz/public

    <Directory /var/www/ontoviz/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Activer `mod_rewrite` :
```bash
a2enmod rewrite
systemctl restart apache2
```

### Nginx

```nginx
server {
    listen 80;
    server_name ontoviz.local;
    root /var/www/ontoviz/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Architecture MVC

```
ontoviz/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php      # Page d'accueil / upload
│   │   └── OntologyController.php  # Upload, parse, serve graph JSON
│   ├── Core/
│   │   ├── Controller.php          # Base controller (view, json, redirect)
│   │   ├── Request.php             # Abstraction requête HTTP
│   │   └── Router.php              # Routeur léger GET/POST + params
│   ├── Models/
│   │   └── OntologyParser.php      # Parser OWL/XML-RDF et Turtle
│   └── Views/
│       ├── layouts/
│       │   └── main.php            # Layout HTML principal
│       ├── home.php                # Page d'accueil + drop zone upload
│       └── visualize.php          # Interface D3.js complète
├── examples/
│   ├── animals.owl                 # Exemple OWL/XML — Animal Kingdom
│   └── foaf.ttl                    # Exemple Turtle — FOAF ontology
├── public/
│   ├── index.php                   # Front controller + bootstrap
│   ├── .htaccess                   # Réécriture Apache
│   └── uploads/                    # Fichiers uploadés (auto-créé)
├── routes/
│   └── web.php                     # Définition des routes
├── vendor/                         # Autoloader Composer (PSR-4)
├── composer.json
└── router.php                      # Script pour php -S (dev)
```

---

## API JSON interne

| Route | Méthode | Description |
|-------|---------|-------------|
| `POST /upload` | POST multipart | Upload + parse un fichier OWL/RDF/TTL |
| `GET /visualize?token=X` | GET | Page de visualisation |
| `GET /api/graph?token=X` | GET | Retourne le graphe JSON parsé |
| `GET /api/demo` | GET | Pizza Ontology de démonstration |
| `POST /api/parse` | POST JSON | Parse un contenu inline |

### Format de réponse graphe

```json
{
  "nodes": [
    { "id": 0, "uri": "...", "label": "Pizza", "type": "class", "comment": "...", "properties": {} }
  ],
  "edges": [
    { "source": 2, "target": 1, "predicate": "rdfs:subClassOf", "label": "subClassOf", "type": "subClassOf" }
  ],
  "stats": {
    "totalNodes": 24,
    "totalEdges": 31,
    "nodeTypes": { "class": 20, "objectProperty": 3 },
    "edgeTypes": { "subClassOf": 19 }
  },
  "namespaces": { "http://www.w3.org/2002/07/owl#": "owl" }
}
```

---

##  Formats supportés

| Format | Extension | Notes |
|--------|-----------|-------|
| OWL/XML | `.owl` | Syntax XML-RDF de référence |
| RDF/XML | `.rdf`, `.xml` | Standard W3C |

---

## Stack technique

- **PHP 8.5** — MVC maison, PSR-4 Autoload, `simplexml` pour OWL
- **D3.js v7** — Force simulation, Partition/Sunburst, Tree layout
- **CSS Variables** — Thème dark sans dépendances externes
- **Google Fonts** — Syne (titres), DM Sans (UI), DM Mono (code)
- **Composer** — Autoloader uniquement (zéro dépendances runtime)

---

## Tests rapides

```bash
# Démarrer le serveur
php -S localhost:8080 -t public router.php

# Tester l'API démo
curl http://localhost:8080/api/demo | python3 -m json.tool | head -40

# Uploader un fichier OWL
curl -X POST http://localhost:8080/upload \
  -F "ontology=@examples/animals.owl" | python3 -m json.tool

# Tester le parsing Turtle
curl -X POST http://localhost:8080/api/parse \
  -H "Content-Type: application/json" \
  -d '{"content":"@prefix ex: <http://ex.org/> . ex:Dog rdfs:subClassOf ex:Animal .", "filename":"test.ttl"}'
```
