<?php

declare(strict_types=1);

namespace App\Models;

class OntologyParser
{
    private array $nodes = [];
    private array $edges = [];
    private array $namespaces = [];
    private array $nodeIndex = [];
    private int $nodeCounter = 0;

    private const NS_MAP = [
        'http://www.w3.org/2002/07/owl#'                => 'owl',
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#'   => 'rdf',
        'http://www.w3.org/2000/01/rdf-schema#'          => 'rdfs',
        'http://www.w3.org/2001/XMLSchema#'              => 'xsd',
        'http://www.w3.org/2004/02/skos/core#'           => 'skos',
        'http://purl.org/dc/elements/1.1/'               => 'dc',
        'http://purl.org/dc/terms/'                      => 'dcterms',
    ];

    public function parseFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Cannot read file: {$filePath}");
        }

        return $this->parse($content, $filePath);
    }

    public function parseContent(string $content, string $filename = 'ontology'): array
    {
        return $this->parse($content, $filename);
    }

    private function parse(string $content, string $filename): array
    {
        $this->reset();

        $ext     = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $trimmed = ltrim($content);

        $isJsonLd = in_array($ext, ['jsonld', 'json'])
            || str_starts_with($trimmed, '[')
            || str_starts_with($trimmed, '{');

        if ($isJsonLd) {
            $this->parseJsonLd($content);
        } else {
            $this->parseXmlRdf($content);
        }

        return $this->buildGraph();
    }

    private function parseXmlRdf(string $content): void
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);

        if ($xml === false) {
            $errors = libxml_get_errors();
            $msg = array_map(fn($e) => trim($e->message), $errors);
            throw new \RuntimeException("XML parse error: " . implode('; ', $msg));
        }

        $namespaces = $xml->getNamespaces(true);
        foreach ($namespaces as $prefix => $uri) {
            $this->namespaces[$uri] = $prefix ?: 'default';
        }

        $this->walkXmlNode($xml, $namespaces);
    }

    private function walkXmlNode(\SimpleXMLElement $node, array $namespaces): void
    {
        foreach ($namespaces as $prefix => $uri) {
            $children = $node->children($uri);
            foreach ($children as $localName => $child) {
                $this->processXmlElement($localName, $uri, $prefix, $child, $namespaces);
            }
        }

        foreach ($node->children() as $localName => $child) {
            $this->processXmlElement($localName, '', '', $child, $namespaces);
        }
    }

    private function processXmlElement(string $localName, string $nsUri, string $prefix, \SimpleXMLElement $el, array $namespaces): void
    {
        $type = $this->buildQName($nsUri, $localName);

        $rdfNs = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $attrs = $el->attributes($rdfNs);
        $about = (string)($attrs['about'] ?? $attrs['ID'] ?? '');

        if (empty($about)) {
            $about = (string)($el->attributes()['about'] ?? '');
        }

        if (empty($about)) {
            $about = '_:b' . $this->nodeCounter++;
        }

        $label = $this->extractLabel($about);
        $comment = '';
        $nodeType = $this->classifyType($type);

        $rdfsNs = 'http://www.w3.org/2000/01/rdf-schema#';
        $rdfsChildren = $el->children($rdfsNs);
        if (!empty($rdfsChildren->label)) {
            $label = (string)$rdfsChildren->label;
        }
        if (!empty($rdfsChildren->comment)) {
            $comment = (string)$rdfsChildren->comment;
        }

        if (!str_starts_with($about, '_:b') || $nodeType !== 'blank') {
            $nodeId = $this->addNode($about, $label, $nodeType, $type, $comment);
        } else {
            $nodeId = null;
        }

        foreach ($namespaces as $pPrefix => $pUri) {
            foreach ($el->children($pUri) as $propName => $propEl) {
                $propQName = $this->buildQName($pUri, $propName);
                $propAttrs = $propEl->attributes($rdfNs);
                $targetUri = (string)($propAttrs['resource'] ?? '');

                if (!empty($targetUri) && $nodeId !== null) {
                    $targetId = $this->addNode($targetUri, $this->extractLabel($targetUri), $this->getRelationType($propQName), $propQName);
                    $this->addEdge($nodeId, $targetId, $propQName, $this->getEdgeLabel($propQName));
                } elseif ($nodeId !== null) {
                    $value = (string)$propEl;
                    if (!empty($value) && !in_array($propQName, ['rdfs:label', 'rdfs:comment'])) {
                        $this->nodes[$nodeId]['properties'][$propQName] = $value;
                    }
                }
            }
        }

        foreach ($el->children($rdfNs) as $childName => $childEl) {
            if ($childName === 'Description' || $childName === 'type') {
                continue;
            }
        }
    }

    private function parseJsonLd(string $content): void
    {
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $items = [];
        if (isset($data['@graph'])) {
            $items = $data['@graph'];
            if (isset($data['@context']) && is_array($data['@context'])) {
                foreach ($data['@context'] as $prefix => $uri) {
                    if (is_string($uri) && str_contains($uri, '://')) {
                        $this->namespaces[$uri] = $prefix;
                    }
                }
            }
        } elseif (is_array($data) && isset($data[0])) {
            $items = $data; 
        } elseif (is_array($data)) {
            $items = [$data];
        }

        $OWL   = 'http://www.w3.org/2002/07/owl#';
        $RDFS  = 'http://www.w3.org/2000/01/rdf-schema#';
        $RDF   = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

        foreach ($items as $item) {
            $id    = $item['@id'] ?? null;
            if (!$id) continue;

            $types    = $item['@type'] ?? [];
            $typeStr  = is_array($types) ? ($types[0] ?? '') : $types;
            $nodeType = $this->classifyType($this->extractLabel($typeStr));

            $label   = $this->extractLabel($id);
            $labelRaw = $item[$RDFS . 'label'] ?? $item['rdfs:label'] ?? null;
            if ($labelRaw) {
                $label = $this->extractJsonLdValue($labelRaw);
            }

            $comment    = '';
            $commentRaw = $item[$RDFS . 'comment'] ?? $item['rdfs:comment'] ?? null;
            if ($commentRaw) {
                $comment = $this->extractJsonLdValue($commentRaw);
            }

            $isBlank = str_starts_with($id, '_:');
            if ($isBlank && $nodeType === 'restriction') continue;

            $this->addNode($id, $label, $nodeType, $typeStr, $comment);
        }

        foreach ($items as $item) {
            $srcId = $item['@id'] ?? null;
            if (!$srcId || !isset($this->nodeIndex[$srcId])) continue;

            $srcNodeId = $this->nodeIndex[$srcId];

            foreach ($item as $predicate => $values) {
                if (in_array($predicate, ['@id', '@type'])) continue;

                $values = is_array($values) ? $values : [$values];

                foreach ($values as $val) {
                    if (!is_array($val)) continue;

                    $targetUri = $val['@id'] ?? null;
                    if ($targetUri && isset($this->nodeIndex[$targetUri])) {
                        $tgtNodeId  = $this->nodeIndex[$targetUri];
                        $edgeLabel  = $this->getEdgeLabel($predicate);
                        $edgeType   = $this->classifyEdgeType($predicate);
                        $this->addEdge($srcNodeId, $tgtNodeId, $predicate, $edgeLabel);
                    } elseif (isset($val['@value'])) {
                        $propLabel = $this->extractLabel($predicate);
                        if (!in_array($propLabel, ['label', 'comment'])) {
                            $this->nodes[$srcNodeId]['properties'][$propLabel] = (string)$val['@value'];
                        }
                    }
                }
            }

            $types = $item['@type'] ?? [];
            if (is_string($types)) $types = [$types];
            foreach ($types as $typeUri) {
                if (!str_starts_with($typeUri, $OWL) && !str_starts_with($typeUri, $RDF)
                    && isset($this->nodeIndex[$typeUri])) {
                    $tgtId = $this->nodeIndex[$typeUri];
                    $this->addEdge($srcNodeId, $tgtId, $RDF . 'type', 'type');
                }
            }
        }
    }

    private function extractJsonLdValue(mixed $raw): string
    {
        if (is_string($raw)) return $raw;
        if (is_array($raw)) {
            $first = $raw[0] ?? $raw;
            if (is_string($first)) return $first;
            if (is_array($first)) return (string)($first['@value'] ?? $first['@id'] ?? '');
        }
        return '';
    }

    private function buildQName(string $nsUri, string $localName): string
    {
        $prefix = self::NS_MAP[$nsUri] ?? ($this->namespaces[$nsUri] ?? null);
        if ($prefix) {
            return "{$prefix}:{$localName}";
        }
        return empty($nsUri) ? $localName : "{$nsUri}{$localName}";
    }

    private function extractLabel(string $uri): string
    {
        if (empty($uri) || $uri === 'a') return 'unknown';

        if (str_contains($uri, '#')) {
            return substr($uri, strrpos($uri, '#') + 1);
        }
        if (str_contains($uri, '/')) {
            $seg = substr($uri, strrpos($uri, '/') + 1);
            if (!empty($seg)) return $seg;
        }
        if (str_contains($uri, ':')) {
            return substr($uri, strrpos($uri, ':') + 1);
        }

        return $uri;
    }

    private function classifyType(string $type): string
    {
        return match (true) {
            str_contains($type, 'Class')           => 'class',
            str_contains($type, 'ObjectProperty')  => 'objectProperty',
            str_contains($type, 'DatatypeProperty') => 'datatypeProperty',
            str_contains($type, 'AnnotationProperty') => 'annotationProperty',
            str_contains($type, 'Individual')      => 'individual',
            str_contains($type, 'Restriction')     => 'restriction',
            str_contains($type, 'Ontology')        => 'ontology',
            default                                 => 'resource',
        };
    }

    private function getResourceType(string $predUri, string $objUri): string
    {
        if (str_contains($predUri, 'type')) {
            return $this->classifyType($objUri);
        }
        return 'resource';
    }

    private function getRelationType(string $propUri): string
    {
        return match (true) {
            str_contains($propUri, 'subClassOf')        => 'class',
            str_contains($propUri, 'domain')             => 'class',
            str_contains($propUri, 'range')              => 'class',
            str_contains($propUri, 'type')               => 'class',
            str_contains($propUri, 'ObjectProperty')     => 'objectProperty',
            str_contains($propUri, 'DatatypeProperty')   => 'datatypeProperty',
            default                                       => 'resource',
        };
    }

    private function getEdgeLabel(string $uri): string
    {
        $raw = $this->extractLabel($uri);
        return preg_replace('/([A-Z])/', ' $1', $raw) ?: $raw;
    }

    private function addNode(string $uri, string $label, string $type, string $rdfType = '', string $comment = ''): int
    {
        if (isset($this->nodeIndex[$uri])) {
            $id = $this->nodeIndex[$uri];
            if ($this->nodes[$id]['type'] === 'resource' && $type !== 'resource') {
                $this->nodes[$id]['type'] = $type;
            }
            return $id;
        }

        $id = count($this->nodes);
        $this->nodeIndex[$uri] = $id;
        $this->nodes[$id] = [
            'id'         => $id,
            'uri'        => $uri,
            'label'      => $label,
            'type'       => $type,
            'rdfType'    => $rdfType,
            'comment'    => $comment,
            'properties' => [],
        ];

        return $id;
    }

    private function addEdge(int $source, int $target, string $predicate, string $label): void
    {
        if ($source === $target) return;

        $key = "{$source}-{$target}-{$predicate}";
        foreach ($this->edges as $edge) {
            if ($edge['source'] === $source && $edge['target'] === $target && $edge['predicate'] === $predicate) {
                return;
            }
        }

        $this->edges[] = [
            'source'    => $source,
            'target'    => $target,
            'predicate' => $predicate,
            'label'     => trim($label),
            'type'      => $this->classifyEdgeType($predicate),
        ];
    }

    private function classifyEdgeType(string $predicate): string
    {
        return match (true) {
            str_contains($predicate, 'subClassOf')       => 'subClassOf',
            str_contains($predicate, 'subPropertyOf')    => 'subPropertyOf',
            str_contains($predicate, 'type')             => 'type',
            str_contains($predicate, 'domain')           => 'domain',
            str_contains($predicate, 'range')            => 'range',
            str_contains($predicate, 'equivalentClass')  => 'equivalentClass',
            str_contains($predicate, 'disjointWith')     => 'disjointWith',
            str_contains($predicate, 'onProperty')       => 'onProperty',
            default                                       => 'relation',
        };
    }

    private function buildGraph(): array
    {
        $stats = $this->computeStats();

        return [
            'nodes'      => array_values($this->nodes),
            'edges'      => $this->edges,
            'stats'      => $stats,
            'namespaces' => $this->namespaces,
        ];
    }

    private function computeStats(): array
    {
        $typeCounts = [];
        foreach ($this->nodes as $node) {
            $t = $node['type'];
            $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
        }

        $edgeTypeCounts = [];
        foreach ($this->edges as $edge) {
            $t = $edge['type'];
            $edgeTypeCounts[$t] = ($edgeTypeCounts[$t] ?? 0) + 1;
        }

        return [
            'totalNodes'    => count($this->nodes),
            'totalEdges'    => count($this->edges),
            'nodeTypes'     => $typeCounts,
            'edgeTypes'     => $edgeTypeCounts,
            'namespaceCount' => count($this->namespaces),
        ];
    }

    private function reset(): void
    {
        $this->nodes       = [];
        $this->edges       = [];
        $this->namespaces  = [];
        $this->nodeIndex   = [];
        $this->nodeCounter = 0;
    }
}
