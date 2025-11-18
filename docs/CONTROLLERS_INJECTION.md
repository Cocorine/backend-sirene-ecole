# Guide des Controllers - Injection de Dépendances

## 📚 Table des matières

1. [Introduction](#introduction)
2. [Constructor Injection (avec binding)](#constructor-injection-avec-binding)
3. [Method Injection (sans binding)](#method-injection-sans-binding)
4. [Comparaison des approches](#comparaison-des-approches)
5. [Quand utiliser quelle approche ?](#quand-utiliser-quelle-approche)
6. [Exemples complets](#exemples-complets)
7. [Bonnes pratiques](#bonnes-pratiques)

---

## Introduction

Dans Laravel, il existe **deux approches principales** pour injecter des dépendances dans les Controllers :

1. **Constructor Injection** : Injection dans le constructeur (avec binding/propriétés)
2. **Method Injection** : Injection directe dans les méthodes (sans binding)

Les deux approches sont valides et ont leurs cas d'usage !

---

## Constructor Injection (avec binding)

### 🎯 Concept

Les dépendances sont injectées dans le **constructeur** et stockées dans des **propriétés privées**.

### ✅ Avantages

- ✅ Dépendances disponibles dans **toutes les méthodes** du controller
- ✅ Code plus **DRY** (Don't Repeat Yourself)
- ✅ Facile à **mocker** dans les tests
- ✅ Propriétés **typées** (PHP 8.0+)
- ✅ **Readonly** possible (PHP 8.1+)

### ❌ Inconvénients

- ❌ Toutes les dépendances sont instanciées même si **non utilisées**
- ❌ Peut devenir **lourd** si beaucoup de dépendances
- ❌ Coupling plus fort au controller

---

### Exemple complet

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEcoleRequest;
use App\Http\Requests\UpdateEcoleRequest;
use App\Http\Resources\EcoleResource;
use App\Services\Contracts\EcoleServiceInterface;
use App\Services\Contracts\AbonnementServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcoleController extends Controller
{
    use JsonResponseTrait;

    /**
     * Constructor Injection avec propriétés typées readonly
     */
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService,
        private readonly AbonnementServiceInterface $abonnementService
    ) {}

    /**
     * Lister toutes les écoles
     */
    public function index(Request $request): JsonResponse
    {
        // ✅ Utilise $this->ecoleService
        $ecoles = $this->ecoleService->getAll($request->query());

        return $this->successResponse(
            EcoleResource::collection($ecoles),
            'Écoles récupérées avec succès'
        );
    }

    /**
     * Afficher une école
     */
    public function show(string $id): JsonResponse
    {
        // ✅ Utilise $this->ecoleService
        $ecole = $this->ecoleService->find($id);

        if (!$ecole) {
            return $this->notFoundResponse('École non trouvée');
        }

        return $this->successResponse(
            new EcoleResource($ecole),
            'École récupérée avec succès'
        );
    }

    /**
     * Créer une école
     */
    public function store(CreateEcoleRequest $request): JsonResponse
    {
        // ✅ Utilise $this->ecoleService ET $this->abonnementService
        $ecole = $this->ecoleService->create($request->validated());

        // Créer l'abonnement initial
        $this->abonnementService->createForEcole($ecole->id);

        return $this->createdResponse(
            new EcoleResource($ecole->fresh('abonnements')),
            'École créée avec succès'
        );
    }

    /**
     * Mettre à jour une école
     */
    public function update(UpdateEcoleRequest $request, string $id): JsonResponse
    {
        // ✅ Utilise $this->ecoleService
        $ecole = $this->ecoleService->update($id, $request->validated());

        return $this->successResponse(
            new EcoleResource($ecole),
            'École mise à jour avec succès'
        );
    }

    /**
     * Supprimer une école
     */
    public function destroy(string $id): JsonResponse
    {
        // ✅ Utilise $this->ecoleService
        $this->ecoleService->delete($id);

        return $this->successResponse(
            null,
            'École supprimée avec succès'
        );
    }
}
```

---

### Variation : PHP 7.4 (sans readonly)

```php
class EcoleController extends Controller
{
    use JsonResponseTrait;

    private EcoleServiceInterface $ecoleService;
    private AbonnementServiceInterface $abonnementService;

    public function __construct(
        EcoleServiceInterface $ecoleService,
        AbonnementServiceInterface $abonnementService
    ) {
        $this->ecoleService = $ecoleService;
        $this->abonnementService = $abonnementService;
    }

    public function index(Request $request): JsonResponse
    {
        $ecoles = $this->ecoleService->getAll($request->query());
        return $this->successResponse(EcoleResource::collection($ecoles));
    }
}
```

---

## Method Injection (sans binding)

### 🎯 Concept

Les dépendances sont injectées **directement dans chaque méthode** qui en a besoin.

### ✅ Avantages

- ✅ **Lazy loading** : dépendances instanciées uniquement quand nécessaires
- ✅ Pas de **propriétés** dans le controller
- ✅ Chaque méthode est **indépendante**
- ✅ Plus **léger** pour les controllers avec peu de méthodes
- ✅ **Explicite** : on voit immédiatement les dépendances de chaque méthode

### ❌ Inconvénients

- ❌ **Duplication** si plusieurs méthodes utilisent la même dépendance
- ❌ Signature de méthode peut devenir **longue**
- ❌ Moins **DRY**

---

### Exemple complet

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEcoleRequest;
use App\Http\Requests\UpdateEcoleRequest;
use App\Http\Resources\EcoleResource;
use App\Services\Contracts\EcoleServiceInterface;
use App\Services\Contracts\AbonnementServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcoleController extends Controller
{
    use JsonResponseTrait;

    /**
     * ✅ Pas de constructeur, pas de propriétés !
     */

    /**
     * Lister toutes les écoles
     */
    public function index(
        Request $request,
        EcoleServiceInterface $ecoleService // ✅ Injection directe
    ): JsonResponse {
        $ecoles = $ecoleService->getAll($request->query());

        return $this->successResponse(
            EcoleResource::collection($ecoles),
            'Écoles récupérées avec succès'
        );
    }

    /**
     * Afficher une école
     */
    public function show(
        string $id,
        EcoleServiceInterface $ecoleService // ✅ Injection directe
    ): JsonResponse {
        $ecole = $ecoleService->find($id);

        if (!$ecole) {
            return $this->notFoundResponse('École non trouvée');
        }

        return $this->successResponse(
            new EcoleResource($ecole),
            'École récupérée avec succès'
        );
    }

    /**
     * Créer une école
     */
    public function store(
        CreateEcoleRequest $request,
        EcoleServiceInterface $ecoleService, // ✅ Injection directe
        AbonnementServiceInterface $abonnementService // ✅ Injection directe
    ): JsonResponse {
        $ecole = $ecoleService->create($request->validated());

        // Créer l'abonnement initial
        $abonnementService->createForEcole($ecole->id);

        return $this->createdResponse(
            new EcoleResource($ecole->fresh('abonnements')),
            'École créée avec succès'
        );
    }

    /**
     * Mettre à jour une école
     */
    public function update(
        UpdateEcoleRequest $request,
        string $id,
        EcoleServiceInterface $ecoleService // ✅ Injection directe
    ): JsonResponse {
        $ecole = $ecoleService->update($id, $request->validated());

        return $this->successResponse(
            new EcoleResource($ecole),
            'École mise à jour avec succès'
        );
    }

    /**
     * Supprimer une école
     */
    public function destroy(
        string $id,
        EcoleServiceInterface $ecoleService // ✅ Injection directe
    ): JsonResponse {
        $ecoleService->delete($id);

        return $this->successResponse(
            null,
            'École supprimée avec succès'
        );
    }
}
```

---

### Ordre des paramètres avec Method Injection

**Important :** Laravel résout automatiquement les dépendances, mais il faut respecter un ordre logique :

```php
public function update(
    UpdateEcoleRequest $request,     // 1️⃣ FormRequest en premier
    string $id,                      // 2️⃣ Paramètres de route
    EcoleServiceInterface $service   // 3️⃣ Dépendances injectées
): JsonResponse {
    // ...
}
```

**Exemple avec plusieurs paramètres de route :**

```php
// Route : PUT /ecoles/{ecoleId}/sites/{siteId}
public function updateSite(
    UpdateSiteRequest $request,       // 1️⃣ FormRequest
    string $ecoleId,                  // 2️⃣ Premier paramètre de route
    string $siteId,                   // 2️⃣ Deuxième paramètre de route
    SiteServiceInterface $siteService // 3️⃣ Dépendances
): JsonResponse {
    $site = $siteService->update($ecoleId, $siteId, $request->validated());
    return $this->successResponse(new SiteResource($site));
}
```

---

## Comparaison des approches

### 📊 Tableau comparatif

| Critère | Constructor Injection | Method Injection |
|---------|----------------------|------------------|
| **Code DRY** | ✅ Excellent | ❌ Répétition |
| **Performance** | ⚠️ Toutes instanciées | ✅ Lazy loading |
| **Lisibilité** | ✅ Centralisé | ✅ Explicite |
| **Testabilité** | ✅ Facile à mocker | ✅ Facile à mocker |
| **Flexibilité** | ❌ Moins flexible | ✅ Très flexible |
| **Coupling** | ⚠️ Plus fort | ✅ Plus faible |
| **Boilerplate** | ⚠️ Constructeur requis | ✅ Minimal |

---

### 🔍 Exemple comparatif

#### Avec Constructor Injection

```php
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $ecoles = $this->ecoleService->getAll($request->query());
        return $this->successResponse(EcoleResource::collection($ecoles));
    }

    public function show(string $id): JsonResponse
    {
        $ecole = $this->ecoleService->find($id);
        return $this->successResponse(new EcoleResource($ecole));
    }

    public function store(CreateEcoleRequest $request): JsonResponse
    {
        $ecole = $this->ecoleService->create($request->validated());
        return $this->createdResponse(new EcoleResource($ecole));
    }
}
```

**Lignes de code :** ~20 lignes
**Répétition :** Aucune
**Dépendances instanciées :** Toujours (même pour show())

---

#### Avec Method Injection

```php
class EcoleController extends Controller
{
    public function index(
        Request $request,
        EcoleServiceInterface $ecoleService
    ): JsonResponse {
        $ecoles = $ecoleService->getAll($request->query());
        return $this->successResponse(EcoleResource::collection($ecoles));
    }

    public function show(
        string $id,
        EcoleServiceInterface $ecoleService
    ): JsonResponse {
        $ecole = $ecoleService->find($id);
        return $this->successResponse(new EcoleResource($ecole));
    }

    public function store(
        CreateEcoleRequest $request,
        EcoleServiceInterface $ecoleService
    ): JsonResponse {
        $ecole = $ecoleService->create($request->validated());
        return $this->createdResponse(new EcoleResource($ecole));
    }
}
```

**Lignes de code :** ~25 lignes
**Répétition :** `EcoleServiceInterface` répété 3 fois
**Dépendances instanciées :** Seulement quand nécessaire

---

## Quand utiliser quelle approche ?

### ✅ Utiliser Constructor Injection QUAND :

1. **Plusieurs méthodes utilisent la même dépendance**

```php
// ✅ BON : EcoleService utilisé dans 5+ méthodes
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    public function index() { /* utilise ecoleService */ }
    public function show() { /* utilise ecoleService */ }
    public function store() { /* utilise ecoleService */ }
    public function update() { /* utilise ecoleService */ }
    public function destroy() { /* utilise ecoleService */ }
}
```

2. **Controller CRUD complet**

```php
// ✅ BON : Controller CRUD standard
class AbonnementController extends Controller
{
    public function __construct(
        private readonly AbonnementServiceInterface $abonnementService
    ) {}

    // Toutes les méthodes CRUD utilisent abonnementService
}
```

3. **Dépendances partagées entre méthodes**

```php
// ✅ BON : Plusieurs services utilisés partout
class PaiementController extends Controller
{
    public function __construct(
        private readonly PaiementServiceInterface $paiementService,
        private readonly AbonnementServiceInterface $abonnementService,
        private readonly NotificationServiceInterface $notificationService
    ) {}

    // Toutes les méthodes utilisent au moins 2 de ces services
}
```

---

### ✅ Utiliser Method Injection QUAND :

1. **Peu de méthodes dans le controller**

```php
// ✅ BON : Seulement 1-2 méthodes
class HealthCheckController extends Controller
{
    public function check(
        DatabaseServiceInterface $databaseService,
        CacheServiceInterface $cacheService
    ): JsonResponse {
        return response()->json([
            'database' => $databaseService->isConnected(),
            'cache' => $cacheService->isConnected(),
        ]);
    }
}
```

2. **Chaque méthode a des dépendances différentes**

```php
// ✅ BON : Dépendances différentes par méthode
class ReportController extends Controller
{
    public function generatePdf(
        string $id,
        PdfServiceInterface $pdfService
    ) {
        return $pdfService->generate($id);
    }

    public function generateExcel(
        string $id,
        ExcelServiceInterface $excelService
    ) {
        return $excelService->generate($id);
    }

    public function generateCsv(
        string $id,
        CsvServiceInterface $csvService
    ) {
        return $csvService->generate($id);
    }
}
```

3. **Actions isolées (single action controllers)**

```php
// ✅ BON : Controller avec une seule action
class SendPasswordResetLinkController extends Controller
{
    public function __invoke(
        SendPasswordResetLinkRequest $request,
        PasswordResetServiceInterface $passwordResetService
    ): JsonResponse {
        $passwordResetService->send($request->validated('email'));

        return response()->json([
            'message' => 'Lien de réinitialisation envoyé.'
        ]);
    }
}
```

4. **Optimisation de la performance**

```php
// ✅ BON : Service lourd utilisé rarement
class StatisticsController extends Controller
{
    public function summary(): JsonResponse
    {
        // Pas besoin de HeavyAnalyticsService ici
        return response()->json(['message' => 'Summary']);
    }

    public function detailed(
        HeavyAnalyticsServiceInterface $analyticsService // Chargé seulement ici
    ): JsonResponse {
        // Service lourd utilisé seulement pour cette méthode
        return response()->json($analyticsService->getDetailedStats());
    }
}
```

---

## Exemples complets

### Exemple 1 : Controller CRUD standard (Constructor Injection)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSireneRequest;
use App\Http\Requests\UpdateSireneRequest;
use App\Http\Resources\SireneResource;
use App\Services\Contracts\SireneServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SireneController extends Controller
{
    use JsonResponseTrait;

    /**
     * ✅ Constructor Injection : SireneService utilisé dans toutes les méthodes
     */
    public function __construct(
        private readonly SireneServiceInterface $sireneService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $sirenes = $this->sireneService->getAll($request->query());
        return $this->successResponse(SireneResource::collection($sirenes));
    }

    public function show(string $id): JsonResponse
    {
        $sirene = $this->sireneService->find($id);
        return $this->successResponse(new SireneResource($sirene));
    }

    public function store(CreateSireneRequest $request): JsonResponse
    {
        $sirene = $this->sireneService->create($request->validated());
        return $this->createdResponse(new SireneResource($sirene));
    }

    public function update(UpdateSireneRequest $request, string $id): JsonResponse
    {
        $sirene = $this->sireneService->update($id, $request->validated());
        return $this->successResponse(new SireneResource($sirene));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->sireneService->delete($id);
        return $this->successResponse(null, 'Sirène supprimée');
    }
}
```

---

### Exemple 2 : Actions spécifiques (Method Injection)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeclarerPanneRequest;
use App\Http\Requests\ResoudrePanneRequest;
use App\Http\Resources\PanneResource;
use App\Services\Contracts\PanneServiceInterface;
use App\Services\Contracts\NotificationServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;

class PanneController extends Controller
{
    use JsonResponseTrait;

    /**
     * ✅ Pas de constructeur : chaque action a ses propres dépendances
     */

    /**
     * Déclarer une panne
     */
    public function declarer(
        DeclarerPanneRequest $request,
        string $sireneId,
        PanneServiceInterface $panneService,
        NotificationServiceInterface $notificationService
    ): JsonResponse {
        $panne = $panneService->declarer($sireneId, $request->validated());

        // Notifier les techniciens
        $notificationService->notifyTechnicians($panne);

        return $this->createdResponse(
            new PanneResource($panne),
            'Panne déclarée avec succès'
        );
    }

    /**
     * Résoudre une panne
     */
    public function resoudre(
        ResoudrePanneRequest $request,
        string $id,
        PanneServiceInterface $panneService
    ): JsonResponse {
        $panne = $panneService->resoudre($id, $request->validated());

        return $this->successResponse(
            new PanneResource($panne),
            'Panne résolue avec succès'
        );
    }
}
```

---

### Exemple 3 : Approche hybride (mixte)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAbonnementRequest;
use App\Http\Requests\RenouvellerAbonnementRequest;
use App\Http\Resources\AbonnementResource;
use App\Services\Contracts\AbonnementServiceInterface;
use App\Services\Contracts\PaiementServiceInterface;
use App\Services\Contracts\NotificationServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    use JsonResponseTrait;

    /**
     * ✅ Constructor Injection pour le service principal
     */
    public function __construct(
        private readonly AbonnementServiceInterface $abonnementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $abonnements = $this->abonnementService->getAll($request->query());
        return $this->successResponse(AbonnementResource::collection($abonnements));
    }

    public function show(string $id): JsonResponse
    {
        $abonnement = $this->abonnementService->find($id);
        return $this->successResponse(new AbonnementResource($abonnement));
    }

    /**
     * ✅ Method Injection pour services spécifiques
     */
    public function renouveler(
        RenouvellerAbonnementRequest $request,
        string $id,
        PaiementServiceInterface $paiementService, // Injection spécifique
        NotificationServiceInterface $notificationService // Injection spécifique
    ): JsonResponse {
        // Utilise le service principal (constructor)
        $abonnement = $this->abonnementService->renouveler($id);

        // Utilise les services injectés
        $paiementService->createForAbonnement($abonnement->id, $request->validated());
        $notificationService->notifyRenewal($abonnement);

        return $this->successResponse(
            new AbonnementResource($abonnement->fresh()),
            'Abonnement renouvelé avec succès'
        );
    }
}
```

---

## Bonnes pratiques

### ✅ À FAIRE

#### 1. Choisir une approche cohérente par controller

```php
// ✅ BON : Cohérent (tout en Constructor Injection)
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    public function index() { /* ... */ }
    public function show() { /* ... */ }
    public function store() { /* ... */ }
}

// ❌ MAUVAIS : Mélange incohérent
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    public function index() {
        // Utilise $this->ecoleService
    }

    public function show(string $id, EcoleServiceInterface $service) {
        // ⚠️ Pourquoi injecter ici alors qu'on a déjà dans le constructeur ?
    }
}
```

---

#### 2. Utiliser readonly avec Constructor Injection (PHP 8.1+)

```php
// ✅ BON : Propriétés readonly
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}
}

// ❌ MOINS BON : Propriétés mutables
class EcoleController extends Controller
{
    public function __construct(
        private EcoleServiceInterface $ecoleService
    ) {}

    // ⚠️ Risque de modification accidentelle
    public function danger() {
        $this->ecoleService = new SomethingElse(); // Possible !
    }
}
```

---

#### 3. Respecter l'ordre des paramètres avec Method Injection

```php
// ✅ BON : Ordre logique
public function update(
    UpdateEcoleRequest $request,    // 1. FormRequest
    string $id,                     // 2. Route params
    EcoleServiceInterface $service  // 3. Dependencies
): JsonResponse { }

// ❌ MAUVAIS : Ordre incorrect
public function update(
    EcoleServiceInterface $service, // ⚠️ Avant le FormRequest
    UpdateEcoleRequest $request,
    string $id
): JsonResponse { }
```

---

#### 4. Typer toutes les dépendances

```php
// ✅ BON : Type hints partout
public function store(
    CreateEcoleRequest $request,
    EcoleServiceInterface $ecoleService
): JsonResponse { }

// ❌ MAUVAIS : Pas de type hints
public function store($request, $ecoleService) { }
```

---

### ❌ À ÉVITER

#### 1. Injecter des dépendances non utilisées

```php
// ❌ MAUVAIS : AbonnementService injecté mais jamais utilisé
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService,
        private readonly AbonnementServiceInterface $abonnementService // ⚠️ Jamais utilisé
    ) {}

    public function index() {
        return $this->ecoleService->getAll(); // Seulement ecoleService utilisé
    }
}

// ✅ BON : Injecter seulement ce qui est utilisé
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    public function index() {
        return $this->ecoleService->getAll();
    }
}
```

---

#### 2. Mélanger les approches sans raison

```php
// ❌ MAUVAIS : Incohérent
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    public function index(Request $request) {
        return $this->ecoleService->getAll(); // Constructor injection
    }

    public function show(string $id, EcoleServiceInterface $service) {
        return $service->find($id); // ⚠️ Method injection alors qu'on a déjà dans le constructeur
    }
}
```

---

#### 3. Trop de dépendances dans le constructeur

```php
// ❌ MAUVAIS : Trop de dépendances (code smell)
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService,
        private readonly AbonnementServiceInterface $abonnementService,
        private readonly SireneServiceInterface $sireneService,
        private readonly PaiementServiceInterface $paiementService,
        private readonly NotificationServiceInterface $notificationService,
        private readonly EmailServiceInterface $emailService,
        private readonly SmsServiceInterface $smsService
    ) {}
}

// ✅ BON : Refactoriser ou utiliser Method Injection
class EcoleController extends Controller
{
    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    // Injecter les autres services seulement quand nécessaire
    public function sendNotification(
        string $id,
        NotificationServiceInterface $notificationService
    ) {
        // ...
    }
}
```

---

## Résumé

### 🎯 Règle générale

| Situation | Approche recommandée |
|-----------|---------------------|
| Controller CRUD complet | Constructor Injection |
| Service utilisé dans 3+ méthodes | Constructor Injection |
| Actions isolées / Single action | Method Injection |
| Chaque méthode a des dépendances différentes | Method Injection |
| Optimisation performance | Method Injection |
| Service lourd rarement utilisé | Method Injection |

---

### 📋 Checklist

Avant de finaliser un Controller :

- [ ] Approche choisie (Constructor vs Method) est cohérente
- [ ] Pas de dépendances inutilisées
- [ ] Propriétés typées et readonly (si Constructor)
- [ ] Ordre des paramètres respecté (si Method)
- [ ] Toutes les dépendances sont typées
- [ ] Controller ne contient pas trop de dépendances (max 3-4)

---

## Prochaines étapes

📖 Consultez aussi :
- [ARCHITECTURE.md](ARCHITECTURE.md) - Principes SOLID
- [DEV_GUIDE.md](DEV_GUIDE.md) - Guide de développement
- [BEST_PRACTICES.md](BEST_PRACTICES.md) - Bonnes pratiques
- [FAQ.md](FAQ.md) - Questions fréquentes
