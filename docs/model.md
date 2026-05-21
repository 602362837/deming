# **Data Model**

The key tables are the following:

- attributes  
- domains  
- measures  
- controls  
- documents  

## **Table Dependencies**

Overview: who uses what.

```mermaid
flowchart LR
    domains -->|"domain_id (1:N)"| measures
    measures -->|"controls[ ] (N:N)"| controls
    controls -->|"measures[ ] (N:N)"| measures
    attributes -.->|"optional"| measures
    attributes -.->|"optional"| controls
    controls -.->|"next_id (self)"| controls
    documents -.->|"optional"| controls
```

The detailed schema below describes the fields of each table.

```mermaid
erDiagram
    domains ||--o{ measures : "domain_id"
    measures }o--o{ controls : "many-to-many"
    attributes }o--o{ measures : "optional"
    attributes }o--o{ controls : "optional"
    controls o|--o| controls : "next_id"
    documents }o--o| controls : "optional"

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

The relationships are as follows:

| Link | Type | Description |
| --- | --- | --- |
| `domains` → `measures` | Foreign key (1:N) | Each measure references its domain via `domain_id` |
| `measures` ↔ `controls` | Many-to-many (bidirectional) | Each measure lists its controls in `controls[]`; each control lists its measures in `measures[]` |
| `attributes` → `measures` | Optional | The `attributes` field of a measure may contain a list of attribute IDs |
| `attributes` → `controls` | Optional | Same for controls |
| `controls` → `controls` | Self-reference via `next_id` | Allows chaining successive campaigns of the same control |

> **Note:** There is no exposed join table for the measures/controls relationship.  
> IDs are directly embedded in each object on both sides.

---

## **attributes**

Attributes are multi-value classification reference sets.  
Each attribute defines a set of tags (prefixed with `#`) that can be associated with measures and controls.

| Field | Type | Description |
| --- | --- | --- |
| `id` | integer | Unique identifier (PK) |
| `name` | string | Name of the taxonomy (e.g., *Security measures*, *Risk_Level*) |
| `values` | string | List of possible values separated by spaces, each prefixed with `#` (e.g., `#Preventive #Detective #Corrective`) |
| `created_at` | datetime | Creation date (ISO 8601, UTC) |
| `updated_at` | datetime | Last modification date |

Example:

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

## **domains**

Domains group measures by thematic area.  
Each domain belongs to a regulatory or methodological framework (`framework`).

| Field | Type | Description |
| --- | --- | --- |
| `id` | integer | Unique identifier (PK) |
| `framework` | string | Reference framework (e.g., `NIS2`, `Vulnerability Management`) |
| `title` | string | Domain name (e.g., *NIS2 Governance and Steering*) |
| `description` | string | Description of the scope covered, often with reference to an article or standard |
| `created_at` | datetime | Creation date |
| `updated_at` | datetime | Last modification date |

Example:

```json
{
  "id": 1,
  "framework": "NIS2",
  "title": "NIS2 Governance and Steering",
  "description": "Strategic and operational steering according to Art. 21.1 and 21.2.a",
  "created_at": "2026-05-17T20:35:52.000000Z",
  "updated_at": "2026-05-17T20:35:52.000000Z"
}
```

---

## **measures**

Security measures describe the requirements to be implemented.  
Each measure belongs to a domain and is verified by one or more controls.

| Field | Type | Description |
| --- | --- | --- |
| `id` | integer | Unique identifier (PK) |
| `domain_id` | integer | Reference to `domains.id` (FK, required) |
| `name` | string | Name of the measure, often with article number (e.g., *Art.21.2.a – Risk Analysis*) |
| `clause` | string | Short identifier of the normative clause (e.g., `NIS2-Art.21.2.a`) |
| `objective` | string | Expected objective of this measure |
| `input` | string \| null | Data or resources required for implementation |
| `model` | string \| null | Recommended operational model or method |
| `indicator` | string \| null | Structured performance indicator (Target, Frequency, Owner) |
| `action_plan` | string \| null | Associated action or treatment plan |
| `standard` | string \| null | Reference to an external standard (e.g., ISO 27001) |
| `attributes` | array \| null | List of associated attribute IDs; `null` if none |
| `controls` | array | List of control IDs verifying this measure |
| `created_at` | datetime | Creation date |
| `updated_at` | datetime | Last modification date |

Example:

```json
{
  "id": 1,
  "domain_id": 1,
  "name": "Art.21.2.a - Risk Analysis",
  "clause": "NIS2-Art.21.2.a",
  "objective": "Assessment of threats to critical assets using EBIOS RM or equivalent methodology",
  "input": "List of critical assets, EBIOS RM methodology",
  "model": "Annual analysis according to ISO 27005 or EBIOS RM",
  "indicator": "Target: Residual score ≤ acceptable | Frequency: Annual | Owner: CISO",
  "action_plan": "Risk treatment plan approved by Management",
  "standard": null,
  "attributes": null,
  "controls": [1]
}
```

---

## **controls**

Controls describe periodic operational verifications.  
A control checks whether one or more measures are properly applied.  
It contains planning, execution, and result data.

| Field | Type | Description |
| --- | --- | --- |
| `id` | integer | Unique identifier (PK) |
| `name` | string | Title of the verification |
| `objective` | string \| null | Specific objective of this control |
| `input` | string \| null | Data or evidence required for execution |
| `model` | string \| null | Operating procedure |
| `indicator` | string \| null | Result indicator |
| `action_plan` | string \| null | Corrective actions if the control fails |
| `periodicity` | integer \| null | Frequency in months (e.g., `12` = annual, `3` = quarterly) |
| `plan_date` | date \| null | Planned execution date (`YYYY-MM-DD`) |
| `realisation_date` | date \| null | Actual execution date of the last control |
| `observations` | string \| null | Free comments on the result |
| `score` | number \| null | Numeric score from the evaluation; `null` if not performed |
| `note` | string \| null | Additional qualitative note |
| `status` | integer | Current status of the control (see below) |
| `next_id` | integer \| null | ID of the next control in the historical chain (self FK) |
| `standard` | string \| null | External normative reference |
| `attributes` | array \| null | List of associated attribute IDs; `null` if none |
| `scope` | string \| null | Scope of application (entity, site, system) |
| `measures` | array | List of measure IDs verified by this control |
| `created_at` | datetime | Creation date |
| `updated_at` | datetime | Last modification date |

### **Values of the `status` field**

| Value | Meaning |
| --- | --- |
| `0` | Not performed / To be planned |
| `1` | In progress |
| `2` | Performed |
| `3` | Validated / Compliant |
| `4` | Non-compliant |

Example:

```json
{
  "id": 1,
  "name": "Formal review and signature of the risk analysis",
  "objective": "Management validation of the risk treatment strategy",
  "model": "Executive Committee presentation + formal signature",
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

## **documents**

The `documents` table is intended to store attachments and documentary evidence associated with controls or measures.
