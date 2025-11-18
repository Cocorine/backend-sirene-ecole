# Guide Avancé des Form Requests

## 📚 Table des matières

1. [Introduction](#introduction)
2. [prepareForValidation() - Préparer les données](#prepareforvalidation---préparer-les-données)
3. [withValidator() - Validation personnalisée](#withvalidator---validation-personnalisée)
4. [Validation avec Enums](#validation-avec-enums)
5. [Rules personnalisées](#rules-personnalisées)
6. [Rules avec Callbacks](#rules-avec-callbacks)
7. [Validation conditionnelle](#validation-conditionnelle)
8. [Messages d'erreur personnalisés](#messages-derreur-personnalisés)
9. [Attributs personnalisés](#attributs-personnalisés)
10. [Gestion des erreurs de validation](#gestion-des-erreurs-de-validation)
11. [Exemples complets du projet](#exemples-complets-du-projet)
12. [Bonnes pratiques](#bonnes-pratiques)

---

## Introduction

Les **Form Requests** de Laravel offrent bien plus que la simple validation. Ce guide explore toutes les fonctionnalités avancées pour créer des validations robustes et maintenables.

---

## prepareForValidation() - Préparer les données

### 🎯 Qu'est-ce que prepareForValidation() ?

Cette méthode permet de **modifier ou nettoyer les données** AVANT la validation.

### Cas d'usage courants

#### 1️⃣ Nettoyer et formater les données

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEcoleRequest extends FormRequest
{
    /**
     * Préparer les données avant validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Nettoyer le numéro de téléphone
            'telephone' => $this->cleanPhoneNumber($this->telephone),

            // Convertir en minuscules
            'email' => strtolower($this->email),

            // Formater le code postal
            'code_postal' => str_pad($this->code_postal, 5, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Nettoyer un numéro de téléphone
     */
    private function cleanPhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Retirer tous les caractères non numériques sauf le +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Ajouter le préfixe +225 si absent
        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+225' . ltrim($cleaned, '0');
        }

        return $cleaned;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:ecoles',
            'telephone' => 'required|string|regex:/^\+225[0-9]{10}$/',
            'code_postal' => 'required|string|size:5',
        ];
    }
}
```

**Exemple d'utilisation :**

```php
// Entrée utilisateur :
{
    "nom": "École Primaire",
    "email": "CONTACT@Ecole.FR",
    "telephone": "01 23 45 67 89",
    "code_postal": "123"
}

// Après prepareForValidation() :
{
    "nom": "École Primaire",
    "email": "contact@ecole.fr",        // ✅ Converti en minuscules
    "telephone": "+22501234567890",     // ✅ Nettoyé et formaté
    "code_postal": "00123"              // ✅ Complété avec des zéros
}
```

---

#### 2️⃣ Ajouter des données calculées

```php
class CreateAbonnementRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Calculer la date de fin (1 an après le début)
            'date_fin' => $this->date_debut
                ? Carbon::parse($this->date_debut)->addYear()
                : null,

            // Ajouter le montant par défaut
            'montant' => $this->montant ?? config('abonnement.price_per_year'),

            // Générer un code unique
            'reference' => $this->reference ?? 'ABO-' . time(),
        ]);
    }

    public function rules(): array
    {
        return [
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'montant' => 'required|numeric|min:0',
            'reference' => 'required|string|unique:abonnements',
        ];
    }
}
```

---

#### 3️⃣ Convertir des valeurs booléennes

```php
class UpdateEcoleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Convertir string "true"/"false" en boolean
            'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),

            // Convertir "1"/"0" en boolean
            'accept_newsletter' => (bool) $this->accept_newsletter,
        ]);
    }

    public function rules(): array
    {
        return [
            'is_active' => 'sometimes|boolean',
            'accept_newsletter' => 'sometimes|boolean',
        ];
    }
}
```

---

#### 4️⃣ Extraire des données de route

```php
class UpdateSireneRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Ajouter l'ID de la sirène depuis l'URL
        $this->merge([
            'sirene_id' => $this->route('id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'sirene_id' => 'required|exists:sirenes,id',
            'numero_serie' => 'required|string',
            // ...
        ];
    }
}
```

---

## withValidator() - Validation personnalisée

### 🎯 Qu'est-ce que withValidator() ?

Cette méthode permet d'ajouter des **validations personnalisées** après les règles standards.

### Cas d'usage courants

#### 1️⃣ Validation croisée entre champs

```php
class CreatePaiementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'abonnement_id' => 'required|exists:abonnements,id',
            'montant' => 'required|numeric|min:0',
            'moyen_paiement' => 'required|in:especes,carte,cheque,mobile_money',
            'reference_transaction' => 'nullable|string',
        ];
    }

    /**
     * Ajouter des validations personnalisées
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Vérifier que le montant correspond à l'abonnement
            $abonnement = Abonnement::find($this->abonnement_id);

            if ($abonnement && $this->montant != $abonnement->montant) {
                $validator->errors()->add(
                    'montant',
                    'Le montant doit être de ' . $abonnement->montant . ' FCFA.'
                );
            }

            // Mobile Money nécessite une référence
            if ($this->moyen_paiement === 'mobile_money' && !$this->reference_transaction) {
                $validator->errors()->add(
                    'reference_transaction',
                    'Une référence de transaction est obligatoire pour Mobile Money.'
                );
            }
        });
    }
}
```

---

#### 2️⃣ Validation métier complexe

```php
class RenouvellerAbonnementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'abonnement_id' => 'required|exists:abonnements,id',
            'moyen_paiement' => 'required|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $abonnement = Abonnement::find($this->abonnement_id);

            if (!$abonnement) {
                return;
            }

            // Règle 1 : Ne peut pas renouveler un abonnement suspendu
            if ($abonnement->statut === StatutAbonnement::SUSPENDU) {
                $validator->errors()->add(
                    'abonnement_id',
                    'Impossible de renouveler un abonnement suspendu. Contactez le support.'
                );
            }

            // Règle 2 : Ne peut renouveler que 30 jours avant expiration
            $joursAvantExpiration = now()->diffInDays($abonnement->date_fin, false);

            if ($joursAvantExpiration > 30) {
                $validator->errors()->add(
                    'abonnement_id',
                    "Vous pourrez renouveler dans {$joursAvantExpiration} jours."
                );
            }

            // Règle 3 : L'école doit avoir un abonnement actif
            if (!$abonnement->ecole->hasActiveAbonnement()) {
                $validator->errors()->add(
                    'abonnement_id',
                    'L\'école doit avoir un abonnement actif pour pouvoir le renouveler.'
                );
            }
        });
    }
}
```

---

#### 3️⃣ Validation avec requête en base de données

```php
class AssignTechnicienRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'technicien_id' => 'required|exists:techniciens,id',
            'ordre_mission_id' => 'required|exists:ordres_missions,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $technicien = Technicien::find($this->technicien_id);

            if (!$technicien) {
                return;
            }

            // Vérifier que le technicien n'a pas trop de missions en cours
            $missionsEnCours = $technicien->ordresMissions()
                ->where('statut', StatutMission::OPEN)
                ->count();

            if ($missionsEnCours >= 5) {
                $validator->errors()->add(
                    'technicien_id',
                    'Ce technicien a déjà 5 missions en cours. Choisissez un autre technicien.'
                );
            }

            // Vérifier la disponibilité géographique
            $ordreMission = OrdreMission::find($this->ordre_mission_id);

            if ($ordreMission && $technicien->ville_id !== $ordreMission->ecole->ville_id) {
                $validator->errors()->add(
                    'technicien_id',
                    'Ce technicien n\'est pas disponible dans cette zone géographique.'
                );
            }
        });
    }
}
```

---

## Validation avec Enums

### 🎯 Utiliser les Enums PHP 8.1+ dans la validation

Laravel 12 supporte nativement les Enums dans la validation.

#### 1️⃣ Validation avec Rule::enum()

```php
<?php

namespace App\Http\Requests;

use App\Enums\StatutAbonnement;
use App\Enums\MoyenPaiement;
use App\Enums\StatutSirene;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAbonnementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ecole_id' => 'required|exists:ecoles,id',

            // ✅ Valider avec un Enum
            'statut' => ['required', Rule::enum(StatutAbonnement::class)],

            // ✅ Accepte : "actif", "expire", "suspendu", "en_attente"
            // ❌ Rejette : "invalid", "abc", 123
        ];
    }
}
```

---

#### 2️⃣ Enum avec valeurs multiples acceptées

```php
class UpdatePaiementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'moyen_paiement' => ['required', Rule::enum(MoyenPaiement::class)],
        ];
    }

    /**
     * Messages personnalisés pour Enum
     */
    public function messages(): array
    {
        $moyens = implode(', ', array_column(MoyenPaiement::cases(), 'value'));

        return [
            'moyen_paiement.Illuminate\Validation\Rules\Enum' =>
                "Le moyen de paiement doit être l'un des suivants : {$moyens}.",
        ];
    }
}
```

---

#### 3️⃣ Enum backed (avec valeur)

```php
<?php

namespace App\Enums;

enum StatutAbonnement: string
{
    case ACTIF = 'actif';
    case EXPIRE = 'expire';
    case SUSPENDU = 'suspendu';
    case EN_ATTENTE = 'en_attente';
}

// Dans le FormRequest
class UpdateAbonnementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // ✅ Valide : "actif", "expire", "suspendu", "en_attente"
            'statut' => ['required', Rule::enum(StatutAbonnement::class)],
        ];
    }
}
```

---

#### 4️⃣ Conversion automatique en Enum

```php
class CreateSireneRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::enum(StatutSirene::class)],
        ];
    }

    /**
     * Convertir en Enum après validation
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Convertir le statut string en Enum
        if (isset($data['statut'])) {
            $data['statut'] = StatutSirene::from($data['statut']);
        }

        return $data;
    }
}
```

---

## Rules personnalisées

### 🎯 Créer des règles de validation réutilisables

#### 1️⃣ Rule simple avec classe

```bash
php artisan make:rule ValidPhoneNumber
```

**Fichier : `app/Rules/ValidPhoneNumber.php`**

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhoneNumber implements ValidationRule
{
    /**
     * Valider la règle
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validation du format téléphone ivoirien
        if (!preg_match('/^\+225[0-9]{10}$/', $value)) {
            $fail('Le numéro de téléphone doit être au format : +225XXXXXXXXXX');
        }
    }
}
```

**Utilisation :**

```php
class CreateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'telephone' => ['required', 'string', new ValidPhoneNumber()],
        ];
    }
}
```

---

#### 2️⃣ Rule avec paramètres

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinAge implements ValidationRule
{
    public function __construct(
        private int $minAge
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $birthDate = \Carbon\Carbon::parse($value);
        $age = $birthDate->diffInYears(now());

        if ($age < $this->minAge) {
            $fail("Vous devez avoir au moins {$this->minAge} ans.");
        }
    }
}
```

**Utilisation :**

```php
class CreateTechnicienRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date_naissance' => ['required', 'date', new MinAge(18)],
        ];
    }
}
```

---

#### 3️⃣ Rule avec accès à la base de données

```php
<?php

namespace App\Rules;

use App\Models\Ecole;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EcoleHasActiveAbonnement implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $ecole = Ecole::find($value);

        if (!$ecole) {
            $fail('École non trouvée.');
            return;
        }

        if (!$ecole->hasActiveAbonnement()) {
            $fail('Cette école n\'a pas d\'abonnement actif.');
        }
    }
}
```

**Utilisation :**

```php
class CreateSireneRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ecole_id' => [
                'required',
                'exists:ecoles,id',
                new EcoleHasActiveAbonnement(),
            ],
        ];
    }
}
```

---

#### 4️⃣ Rule avec contexte (données de la requête)

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueNumeroSerieForModele implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    /**
     * Définir les données de la requête
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $modeleId = $this->data['modele_id'] ?? null;

        if (!$modeleId) {
            return;
        }

        // Vérifier l'unicité du numéro de série pour ce modèle
        $exists = \App\Models\Sirene::where('numero_serie', $value)
            ->where('modele_id', $modeleId)
            ->exists();

        if ($exists) {
            $fail('Ce numéro de série existe déjà pour ce modèle de sirène.');
        }
    }
}
```

**Utilisation :**

```php
class CreateSireneRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'modele_id' => 'required|exists:modeles_sirenes,id',
            'numero_serie' => [
                'required',
                'string',
                new UniqueNumeroSerieForModele(),
            ],
        ];
    }
}
```

---

## Rules avec Callbacks

### 🎯 Validation inline avec fonction

#### 1️⃣ Callback simple

```php
use Illuminate\Validation\Rule;

class CreateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code_etablissement' => [
                'required',
                'string',

                // ✅ Callback inline
                function (string $attribute, mixed $value, Closure $fail) {
                    if (!str_starts_with($value, 'ECO-')) {
                        $fail('Le code établissement doit commencer par "ECO-".');
                    }
                },
            ],
        ];
    }
}
```

---

#### 2️⃣ Callback avec vérification en BDD

```php
class UpdateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        $ecoleId = $this->route('id');

        return [
            'email' => [
                'required',
                'email',

                // ✅ Vérifier l'unicité (sauf pour cet enregistrement)
                function (string $attribute, mixed $value, Closure $fail) use ($ecoleId) {
                    $exists = Ecole::where('email', $value)
                        ->where('id', '!=', $ecoleId)
                        ->exists();

                    if ($exists) {
                        $fail('Cet email est déjà utilisé par une autre école.');
                    }
                },
            ],
        ];
    }
}
```

---

#### 3️⃣ Callback avec logique complexe

```php
class CreateInterventionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'technicien_id' => [
                'required',
                'exists:techniciens,id',

                function (string $attribute, mixed $value, Closure $fail) {
                    $technicien = Technicien::find($value);

                    if (!$technicien) {
                        return;
                    }

                    // Vérifier la disponibilité
                    $missionsEnCours = $technicien->ordresMissions()
                        ->where('statut', StatutMission::OPEN)
                        ->count();

                    if ($missionsEnCours >= 5) {
                        $fail('Ce technicien a déjà 5 missions en cours.');
                        return;
                    }

                    // Vérifier les compétences
                    $panne = Panne::find($this->input('panne_id'));

                    if ($panne && !$technicien->hasCompetence($panne->type_panne)) {
                        $fail('Ce technicien n\'a pas les compétences requises pour cette intervention.');
                    }
                },
            ],
        ];
    }
}
```

---

## Validation conditionnelle

### 🎯 Valider selon des conditions

#### 1️⃣ required_if (Si un autre champ a une valeur)

```php
class CreatePaiementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'moyen_paiement' => 'required|in:especes,carte,cheque,mobile_money',

            // Obligatoire SI moyen_paiement = mobile_money
            'reference_transaction' => 'required_if:moyen_paiement,mobile_money',

            // Obligatoire SI moyen_paiement = cheque
            'numero_cheque' => 'required_if:moyen_paiement,cheque',
            'banque' => 'required_if:moyen_paiement,cheque',

            // Obligatoire SI moyen_paiement = carte
            'last_4_digits' => 'required_if:moyen_paiement,carte',
        ];
    }
}
```

---

#### 2️⃣ sometimes (Seulement si le champ est présent)

```php
class UpdateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Valider seulement si présent dans la requête
            'nom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:ecoles,email,' . $this->route('id'),
            'telephone' => 'sometimes|required|string',
        ];
    }
}
```

---

#### 3️⃣ exclude_if / exclude_unless

```php
class CreateAbonnementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type_paiement' => 'required|in:immediat,differe',

            // Exclure si type_paiement = immediat
            'date_echeance' => 'exclude_if:type_paiement,immediat|required|date|after:today',

            // Inclure seulement si type_paiement = differe
            'nombre_echeances' => 'exclude_unless:type_paiement,differe|required|integer|min:2|max:12',
        ];
    }
}
```

---

#### 4️⃣ Règles conditionnelles avancées avec Rule::when()

```php
use Illuminate\Validation\Rule;

class CreateSireneRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'modele_id' => 'required|exists:modeles_sirenes,id',

            'numero_serie' => [
                'required',
                'string',

                // Ajouter la règle unique SI c'est une création (pas une mise à jour)
                Rule::when(
                    !$this->route('id'), // Condition
                    ['unique:sirenes,numero_serie'], // Règles si vrai
                    ['unique:sirenes,numero_serie,' . $this->route('id')] // Règles si faux
                ),
            ],
        ];
    }
}
```

---

## Messages d'erreur personnalisés

### 🎯 Personnaliser les messages de validation

#### 1️⃣ Messages par règle

```php
class CreateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:ecoles',
            'telephone' => 'required|string',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            // Format : 'champ.règle' => 'message'
            'nom.required' => 'Le nom de l\'école est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser :max caractères.',

            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
        ];
    }
}
```

---

#### 2️⃣ Messages avec variables

```php
class CreateAbonnementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'montant' => 'required|numeric|min:10000|max:100000',
            'duree_mois' => 'required|integer|between:1,12',
        ];
    }

    public function messages(): array
    {
        return [
            // :min et :max sont remplacés automatiquement
            'montant.min' => 'Le montant minimum est de :min FCFA.',
            'montant.max' => 'Le montant maximum est de :max FCFA.',

            // :min et :max pour between
            'duree_mois.between' => 'La durée doit être entre :min et :max mois.',
        ];
    }
}
```

---

#### 3️⃣ Messages dynamiques

```php
class UpdateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:ecoles,email,' . $this->route('id'),
        ];
    }

    public function messages(): array
    {
        $ecole = Ecole::find($this->route('id'));
        $ancienEmail = $ecole?->email ?? 'l\'ancienne adresse';

        return [
            'email.unique' => "Cette adresse email est déjà utilisée. Votre email actuel est {$ancienEmail}.",
        ];
    }
}
```

---

## Attributs personnalisés

### 🎯 Personnaliser les noms de champs dans les messages

```php
class CreateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'email' => 'required|email',
            'telephone' => 'required|string',
            'ville_id' => 'required|exists:villes,id',
        ];
    }

    /**
     * Attributs personnalisés pour les messages
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom de l\'école',
            'email' => 'adresse email',
            'telephone' => 'numéro de téléphone',
            'ville_id' => 'ville',
        ];
    }

    // Sans attributes() :
    // "The ville_id field is required."

    // Avec attributes() :
    // "Le champ ville est obligatoire."
}
```

---

## Gestion des erreurs de validation

### 🎯 Personnaliser la réponse d'erreur

#### 1️⃣ Changer le code HTTP de la réponse

```php
class CreateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nom' => 'required|string',
        ];
    }

    /**
     * Personnaliser la réponse d'erreur de validation
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422) // Unprocessable Entity
        );
    }
}
```

---

#### 2️⃣ Formater les erreurs personnalisées

```php
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateEcoleRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            $errors[] = [
                'field' => $field,
                'messages' => $messages,
            ];
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'validation_errors' => $errors,
            ], 422)
        );
    }
}

// Réponse :
// {
//     "success": false,
//     "message": "Validation échouée",
//     "validation_errors": [
//         {
//             "field": "nom",
//             "messages": ["Le nom est obligatoire."]
//         },
//         {
//             "field": "email",
//             "messages": ["L'email doit être valide.", "L'email est déjà utilisé."]
//         }
//     ]
// }
```

---

#### 3️⃣ Logger les erreurs de validation

```php
use Illuminate\Support\Facades\Log;

class CreateEcoleRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        // Logger les erreurs
        Log::warning('Validation échouée', [
            'request' => $this->all(),
            'errors' => $validator->errors()->toArray(),
            'user_id' => $this->user()?->id,
            'ip' => $this->ip(),
        ]);

        // Comportement par défaut
        parent::failedValidation($validator);
    }
}
```

---

## Exemples complets du projet

### Exemple 1 : CreateEcoleRequest (Complet)

```php
<?php

namespace App\Http\Requests;

use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class CreateEcoleRequest extends FormRequest
{
    /**
     * Autoriser tout le monde (inscription publique)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Préparer les données avant validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->email),
            'telephone' => $this->cleanPhoneNumber($this->telephone),
        ]);
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:ecoles,email',
            'telephone' => ['required', 'string', new ValidPhoneNumber()],
            'adresse' => 'required|string',
            'ville_id' => 'required|exists:villes,id',
            'type_etablissement' => 'required|in:primaire,secondaire,superieur',
        ];
    }

    /**
     * Messages d'erreur
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'école est obligatoire.',
            'email.unique' => 'Cette adresse email est déjà utilisée par une autre école.',
            'ville_id.exists' => 'La ville sélectionnée n\'existe pas.',
        ];
    }

    /**
     * Attributs personnalisés
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom de l\'école',
            'ville_id' => 'ville',
            'type_etablissement' => 'type d\'établissement',
        ];
    }

    /**
     * Nettoyer le numéro de téléphone
     */
    private function cleanPhoneNumber(?string $phone): ?string
    {
        if (!$phone) return null;

        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+225' . ltrim($cleaned, '0');
        }

        return $cleaned;
    }
}
```

---

### Exemple 2 : CreatePaiementRequest (Avec withValidator)

```php
<?php

namespace App\Http\Requests;

use App\Enums\MoyenPaiement;
use App\Models\Abonnement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-paiement') ?? false;
    }

    public function rules(): array
    {
        return [
            'abonnement_id' => 'required|exists:abonnements,id',
            'montant' => 'required|numeric|min:0',
            'moyen_paiement' => ['required', Rule::enum(MoyenPaiement::class)],
            'reference_transaction' => 'required_if:moyen_paiement,mobile_money',
            'numero_cheque' => 'required_if:moyen_paiement,cheque',
            'banque' => 'required_if:moyen_paiement,cheque',
        ];
    }

    /**
     * Validations personnalisées
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Vérifier que le montant correspond à l'abonnement
            $abonnement = Abonnement::find($this->abonnement_id);

            if ($abonnement && $this->montant != $abonnement->montant) {
                $validator->errors()->add(
                    'montant',
                    "Le montant doit être de {$abonnement->montant} FCFA."
                );
            }

            // Vérifier qu'il n'y a pas déjà un paiement validé
            if ($abonnement && $abonnement->hasValidatedPayment()) {
                $validator->errors()->add(
                    'abonnement_id',
                    'Cet abonnement a déjà un paiement validé.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'reference_transaction.required_if' => 'La référence de transaction est obligatoire pour Mobile Money.',
            'numero_cheque.required_if' => 'Le numéro de chèque est obligatoire.',
            'banque.required_if' => 'Le nom de la banque est obligatoire.',
        ];
    }
}
```

---

## Bonnes pratiques

### ✅ À FAIRE

#### 1. Utiliser prepareForValidation() pour nettoyer les données

```php
// ✅ BON
protected function prepareForValidation(): void
{
    $this->merge([
        'email' => strtolower(trim($this->email)),
        'telephone' => $this->cleanPhoneNumber($this->telephone),
    ]);
}

// ❌ MAUVAIS : Nettoyer dans le Controller
public function store(CreateEcoleRequest $request)
{
    $data = $request->validated();
    $data['email'] = strtolower($data['email']); // ⚠️ Trop tard
}
```

---

#### 2. Créer des Rules réutilisables pour la logique complexe

```php
// ✅ BON : Rule réutilisable
class ValidPhoneNumber implements ValidationRule { }

// ❌ MAUVAIS : Regex dupliquée partout
'telephone' => 'required|regex:/^\+225[0-9]{10}$/',
```

---

#### 3. Utiliser withValidator() pour la validation métier

```php
// ✅ BON : Validation métier dans withValidator()
public function withValidator($validator): void
{
    $validator->after(function ($validator) {
        if ($this->montant != $abonnement->montant) {
            $validator->errors()->add('montant', 'Message');
        }
    });
}

// ❌ MAUVAIS : Validation métier dans le Service
class PaiementService {
    public function create(array $data) {
        if ($data['montant'] != $abonnement->montant) {
            throw new ValidationException(...); // ⚠️ Devrait être dans FormRequest
        }
    }
}
```

---

#### 4. Personnaliser les messages d'erreur

```php
// ✅ BON : Messages clairs et en français
public function messages(): array
{
    return [
        'email.unique' => 'Cette adresse email est déjà utilisée.',
    ];
}

// ❌ MAUVAIS : Messages par défaut en anglais
// "The email has already been taken."
```

---

### ❌ À ÉVITER

#### 1. Ne pas mettre de logique métier lourde dans prepareForValidation()

```php
// ❌ MAUVAIS
protected function prepareForValidation(): void
{
    // ⚠️ Trop de logique, requêtes lourdes
    $ecole = Ecole::with('abonnements.paiements')->find($this->ecole_id);
    $montant = $ecole->calculateNextPaymentAmount();
    $this->merge(['montant' => $montant]);
}

// ✅ BON : Logique simple de nettoyage
protected function prepareForValidation(): void
{
    $this->merge([
        'email' => strtolower($this->email),
    ]);
}
```

---

#### 2. Ne pas dupliquer les Rules entre FormRequests

```php
// ❌ MAUVAIS : Duplication
class CreateEcoleRequest {
    'telephone' => 'required|regex:/^\+225[0-9]{10}$/',
}

class UpdateEcoleRequest {
    'telephone' => 'sometimes|regex:/^\+225[0-9]{10}$/', // Dupliqué
}

// ✅ BON : Rule réutilisable
'telephone' => ['required', new ValidPhoneNumber()],
```

---

## Résumé

### 🎯 Méthodes principales des Form Requests

| Méthode | Usage | Quand utiliser |
|---------|-------|----------------|
| `authorize()` | Autorisation | Vérifier les permissions |
| `rules()` | Règles de validation | Définir les validations |
| `prepareForValidation()` | Préparer les données | Nettoyer/formater avant validation |
| `withValidator()` | Validation avancée | Validation métier complexe |
| `messages()` | Messages personnalisés | Clarifier les erreurs |
| `attributes()` | Noms de champs | Franciser les messages |
| `failedValidation()` | Gestion d'erreur | Personnaliser la réponse 422 |

---

### 📋 Checklist Form Request

Avant de finaliser un FormRequest :

- [ ] `authorize()` défini et retourne la bonne valeur
- [ ] Données nettoyées dans `prepareForValidation()` si nécessaire
- [ ] `rules()` complet avec toutes les validations
- [ ] Utilisation d'Enums avec `Rule::enum()` quand applicable
- [ ] Rules personnalisées pour logique réutilisable
- [ ] Validation métier dans `withValidator()` si nécessaire
- [ ] Messages d'erreur personnalisés en français
- [ ] Attributs traduits pour les messages
- [ ] Testé avec des données valides et invalides

---

## Prochaines étapes

📖 Consultez aussi :
- [ARCHITECTURE.md](ARCHITECTURE.md) - Principes SOLID
- [AUTHORIZATION.md](AUTHORIZATION.md) - Guide d'autorisation
- [DEV_GUIDE.md](DEV_GUIDE.md) - Guide de développement
- [BEST_PRACTICES.md](BEST_PRACTICES.md) - Bonnes pratiques
