# Modèle de données

Les tables clés sont les suivantes

- attributes
- domains
- measures
- controls
- documents

## Dépendances entre tables

Vue d'ensemble : qui utilise quoi.

```mermaid
flowchart LR
    domains -->|"domain_id (1:N)"| measures
    measures -->|"controls[ ] (N:N)"| controls
    controls -->|"measures[ ] (N:N)"| measures
    attributes -.->|"optionnel"| measures
    attributes -.->|"optionnel"| controls
    controls -.->|"next_id (self)"| controls
    documents -.->|"optionnel"| controls
```

Le schéma détaillé ci-dessous décrit les champs de chaque table.

```mermaid
erDiagram
    domains ||--o{ measures : "domain_id"
    measures }o--o{ controls : "many-to-many"
    attributes }o--o{ measures : "optionnel"
    attributes }o--o{ controls : "optionnel"
    controls o|--o| controls : "next_id"
    documents }o--o| controls : "optionnel"

    domains {
        int id PK
        string framework
        string title
        string description
    }
    attributes {
        int id PK
        string name
        string values
    }
    measures {
        int id PK
        int domain_id FK
        string name
        string clause
        string objective
        array controls
        array attributes
    }
    controls {
        int id PK
        int next_id FK
        string name
        int periodicity
        date plan_date
        date realisation_date
        int status
        array measures
        array attributes
    }
    documents {
        int id PK
    }
```

Les relations sont les suivantes :

| Lien | Type | Description |
| --- | --- | --- |
| `domains` → `measures` | Clé étrangère (1:N) | Chaque mesure référence son domaine via `domain_id` |
| `measures` ↔ `controls` | Many-to-many (bidirectionnel) | Chaque mesure liste ses contrôles dans `controls[]` ; chaque contrôle liste ses mesures dans `measures[]` |
| `attributes` → `measures` | Optionnel | Le champ `attributes` d'une mesure peut contenir une liste d'IDs d'attributs |
| `attributes` → `controls` | Optionnel | Idem pour les contrôles |
| `controls` → `controls` | Auto-référence via `next_id` | Permet de chaîner les campagnes successives d'un même contrôle |

> **Note :** il n'y a pas de table de jonction exposée pour la relation measures/controls.
> Les IDs sont directement embarqués dans chaque objet des deux côtés.

---

## attributes

Les attributs sont des référentiels de classification multi-valeurs.
Chaque attribut définit un ensemble de tags (préfixés `#`) qui peuvent être associés aux mesures et aux contrôles.

| Champ | Type | Description |
| --- | --- | --- |
| `id` | integer | Identifiant unique (PK) |
| `name` | string | Intitulé de la taxonomie (ex : *Security measures*, *Risk_Level*) |
| `values` | string | Liste de valeurs possibles séparées par des espaces, chacune préfixée `#` (ex : `#Preventive #Detective #Corrective`) |
| `created_at` | datetime | Date de création (ISO 8601, UTC) |
| `updated_at` | datetime | Date de dernière modification |

Exemple :

```json
{
  "id": 1,
  "name": "Security measures",
  "values": "#Preventive #Detective #Corrective",
  "created_at": "2026-05-17T20:35:52.000000Z",
  "updated_at": "2026-05-17T20:35:52.000000Z"
}
```

---

## domains

Les domaines regroupent les mesures par thématique.
Chaque domaine appartient à un cadre réglementaire ou méthodologique (`framework`).

| Champ | Type | Description |
| --- | --- | --- |
| `id` | integer | Identifiant unique (PK) |
| `framework` | string | Référentiel d'appartenance (ex : `NIS2`, `Vulnerability Management`) |
| `title` | string | Nom du domaine (ex : *Pilotage et Gouvernance NIS2*) |
| `description` | string | Description du périmètre couvert, souvent avec référence à l'article ou à la norme |
| `created_at` | datetime | Date de création |
| `updated_at` | datetime | Date de dernière modification |

Exemple :

```json
{
  "id": 1,
  "framework": "NIS2",
  "title": "Pilotage et Gouvernance NIS2",
  "description": "Pilotage stratégique et opérationnel selon Art. 21.1 et 21.2.a",
  "created_at": "2026-05-17T20:35:52.000000Z",
  "updated_at": "2026-05-17T20:35:52.000000Z"
}
```

---

## measures

Les mesures de sécurité décrivent les exigences à mettre en œuvre.
Chaque mesure appartient à un domaine et est vérifiée par un ou plusieurs contrôles.

| Champ | Type | Description |
| --- | --- | --- |
| `id` | integer | Identifiant unique (PK) |
| `domain_id` | integer | Référence vers `domains.id` (FK, obligatoire) |
| `name` | string | Nom de la mesure, souvent avec le numéro d'article (ex : *Art.21.2.a - Analyse de Risques*) |
| `clause` | string | Identifiant court de la clause normative (ex : `NIS2-Art.21.2.a`) |
| `objective` | string | Objectif attendu par cette mesure |
| `input` | string \| null | Données ou ressources nécessaires à la mise en œuvre |
| `model` | string \| null | Modèle ou méthode opérationnelle recommandée |
| `indicator` | string \| null | Indicateur de performance structuré (Target, Frequency, Owner) |
| `action_plan` | string \| null | Plan d'action ou traitement associé |
| `standard` | string \| null | Référence à une norme externe (ex : ISO 27001) |
| `attributes` | array \| null | Liste d'IDs d'attributs associés ; `null` si aucun |
| `controls` | array | Liste des IDs de contrôles qui vérifient cette mesure |
| `created_at` | datetime | Date de création |
| `updated_at` | datetime | Date de dernière modification |

Exemple :

```json
{
  "id": 1,
  "domain_id": 1,
  "name": "Art.21.2.a - Analyse de Risques",
  "clause": "NIS2-Art.21.2.a",
  "objective": "Évaluation des menaces pesant sur les actifs critiques selon méthodologie EBIOS RM ou équivalent",
  "input": "Liste des actifs critiques, méthodologie EBIOS RM",
  "model": "Analyse annuelle selon ISO 27005 ou EBIOS RM",
  "indicator": "Target: Score résiduel ≤ acceptable | Frequency: Annuel | Owner: RSSI",
  "action_plan": "Plan de traitement des risques validé par Direction",
  "standard": null,
  "attributes": null,
  "controls": [1]
}
```

---

## controls

Les contrôles décrivent les vérifications opérationnelles périodiques.
Un contrôle vérifie qu'une ou plusieurs mesures sont bien appliquées.
Il porte les données de planification, de réalisation et de résultat.

| Champ | Type | Description |
| --- | --- | --- |
| `id` | integer | Identifiant unique (PK) |
| `name` | string | Intitulé de la vérification |
| `objective` | string \| null | Objectif spécifique de ce contrôle |
| `input` | string \| null | Données ou preuves nécessaires à la réalisation |
| `model` | string \| null | Mode opératoire du contrôle |
| `indicator` | string \| null | Indicateur de résultat |
| `action_plan` | string \| null | Actions correctives si le contrôle échoue |
| `periodicity` | integer \| null | Fréquence en mois (ex : `12` = annuel, `3` = trimestriel) |
| `plan_date` | date \| null | Date prévue de réalisation (`YYYY-MM-DD`) |
| `realisation_date` | date \| null | Date effective de réalisation du dernier contrôle |
| `observations` | string \| null | Commentaires libres sur le résultat |
| `score` | number \| null | Score numérique issu de l'évaluation ; `null` si non réalisé |
| `note` | string \| null | Note qualitative complémentaire |
| `status` | integer | État courant du contrôle (voir ci-dessous) |
| `next_id` | integer \| null | ID du contrôle suivant dans la chaîne historique (FK self) |
| `standard` | string \| null | Référence normative externe |
| `attributes` | array \| null | Liste d'IDs d'attributs associés ; `null` si aucun |
| `scope` | string \| null | Périmètre d'application (entité, site, système) |
| `measures` | array | Liste des IDs de mesures vérifiées par ce contrôle |
| `created_at` | datetime | Date de création |
| `updated_at` | datetime | Date de dernière modification |

### Valeurs du champ `status`

| Valeur | Signification |
| --- | --- |
| `0` | Non réalisé / À planifier |
| `1` | En cours |
| `2` | Réalisé |
| `3` | Validé / Conforme |
| `4` | Non conforme |

Exemple :

```json
{
  "id": 1,
  "name": "Revue et signature formelle de l'analyse de risques",
  "objective": "Validation par la direction de la stratégie de traitement des risques",
  "model": "Présentation Codir + signature formelle",
  "periodicity": 12,
  "plan_date": "2026-07-31",
  "realisation_date": "2025-03-25",
  "score": null,
  "status": 0,
  "next_id": null,
  "standard": null,
  "attributes": null,
  "scope": null,
  "measures": [1]
}
```

---

## documents

La table `documents` est destinée à stocker les pièces jointes et preuves documentaires associées aux contrôles ou aux mesures.

