# APP RESTAURANT — CAHIER DES CHARGES OFFICIEL
**Application SaaS de gestion des commandes, menus, tables et abonnements**

- **Version** : 1.0 — Document de cadrage officiel
- **Date** : 15 octobre 2026
- **Cible** : Restaurants, maquis et établissements de restauration (notamment en Côte d'Ivoire et Afrique de l'Ouest)
- **Technologies retenues** : Laravel, Inertia.js, React, TypeScript, PostgreSQL
- **Identité visuelle** :
  - Orange principal : `#F97316`
  - Orange foncé : `#C2410C`
  - Fond clair : `#FFF7ED`
  - Texte principal : `#1F2937`
  - Police : Aptos ou police sans-serif équivalente (Inter / Plus Jakarta Sans)

---

## Sommaire
1. Présentation et contexte
2. Objectifs et indicateurs de réussite
3. Périmètre du projet
4. Parties prenantes et rôles
5. Parcours utilisateurs
6. Exigences fonctionnelles détaillées
7. Règles de gestion
8. Modèle de données conceptuel
9. Architecture technique cible
10. Sécurité, confidentialité et conformité
11. Exigences non fonctionnelles
12. Rapports et indicateurs
13. Tests et critères de recette
14. Déploiement, exploitation et sauvegardes
15. Découpage prévisionnel
16. Risques et décisions à arbitrer
17. Livrables attendus
- Annexe A — Statuts recommandés (Préparation & Paiement)
- Annexe B — Identité visuelle

---

## 1. Présentation et contexte

**APP RESTAURANT** est une plateforme SaaS multi-établissements destinée aux restaurants et maquis. Elle digitalise la consultation des menus et la prise de commande au moyen de QR Codes, tout en fournissant aux équipes un outil de gestion des commandes, tables, paiements et performances.

Le client peut accéder au menu depuis le navigateur de son téléphone, sans installer d’application et sans créer obligatoirement de compte. Chaque établissement dispose d’un espace de gestion indépendant. L’éditeur de la plateforme administre les restaurants, les offres et les abonnements.

### 1.1 Problématique
- La prise de commande manuelle peut entraîner des erreurs de transcription.
- Les menus papier sont difficiles à mettre à jour rapidement.
- Le suivi des commandes et des tables manque parfois de visibilité.
- Les responsables ont besoin d’indicateurs fiables pour piloter les ventes.
- L’éditeur SaaS doit gérer les abonnements et sécuriser l’isolation des données entre établissements.

---

## 2. Objectifs et indicateurs de réussite

| Objectif | Indicateur proposé | Méthode de mesure |
| :--- | :--- | :--- |
| **Réduire le temps de prise de commande** | Temps médian entre ouverture du menu et validation | Événements horodatés et tests terrain |
| **Réduire les erreurs** | Taux de commandes corrigées pour erreur | Historique des corrections / commandes |
| **Autonomiser les restaurants** | Part des changements de menu réalisés sans assistance | Journal des actions |
| **Centraliser les commandes** | Part des commandes enregistrées dans la plateforme | Comparaison avec le registre de l’établissement |
| **Améliorer le suivi** | Part des commandes avec statuts horodatés | Données de commandes |
| **Créer des revenus récurrents** | MRR, abonnements actifs et taux de renouvellement | Module SaaS et paiements |

*Note : Les cibles chiffrées seront fixées après une période pilote. Aucune valeur de performance n’est considérée comme garantie à ce stade.*

---

## 3. Périmètre du projet

### 3.1 Inclus dans la version complète
- Administration globale de la plateforme et gestion des restaurants.
- Offres tarifaires, abonnements, échéances, paiements et suspension contrôlée.
- Gestion des établissements, employés, rôles et permissions.
- Menus, catégories, produits, photos, variantes, suppléments et disponibilité.
- Tables, QR Codes par table et QR Code général pour les commandes à emporter.
- Menu public mobile, recherche, panier, validation et suivi de commande.
- Réception des commandes, préparation, retards, annulations et historique.
- Paiements de commandes, journal des encaissements et prévention des doubles encaissements.
- Rapports, exports PDF/Excel, notifications et journal d’audit.
- Tests automatisés, sauvegardes, supervision et procédures de déploiement.

### 3.2 Hors périmètre initial, sauf décision contraire
- Application mobile native iOS ou Android (le parcours client est web responsive et installable en PWA).
- Marketplace multi-restaurants avec livraison mutualisée.
- Gestion complète des achats fournisseurs, paie et comptabilité générale.
- Gestion de flotte de livreurs et optimisation d’itinéraires.
- Intégrations matérielles spécifiques aux caisses ou imprimantes non validées.

---

## 4. Parties prenantes et rôles

| Rôle | Responsabilités | Accès principal |
| :--- | :--- | :--- |
| **Super administrateur plateforme** | Gérer restaurants, offres, abonnements, paiements d’abonnement, suspensions, statistiques globales et paramètres SaaS. | Console globale |
| **Propriétaire / administrateur restaurant** | Configurer le restaurant, les employés, menus, tables, QR Codes, règles et rapports. | Espace restaurant |
| **Manager** | Superviser les opérations, commandes, disponibilité, tables et rapports selon les permissions accordées. | Espace opérationnel |
| **Personnel de service / cuisine** | Consulter les commandes et mettre à jour les statuts autorisés. | Vue commandes / cuisine (KDS) |
| **Caissier** | Enregistrer les paiements de commandes, consulter les soldes et clôturer les opérations autorisées. | Caisse (POS) |
| **Client** | Consulter le menu, composer une commande, suivre son état et demander de l’aide. | Menu public sans compte obligatoire |

*Principe : Les rôles ci-dessus sont des rôles de référence. Le système permet une attribution de permissions par capacité (RBAC/permissions fines), sans se limiter uniquement au nom du rôle.*

---

## 5. Parcours utilisateurs

### 5.1 Client sur place
1. Scanne le QR Code de la table.
2. Le système affiche le menu du restaurant et identifie la table si le QR Code est valide.
3. Le client choisit les produits, variantes, suppléments et quantités.
4. Relit le récapitulatif, ajoute éventuellement un nom, un téléphone ou une instruction.
5. Valide la commande et reçoit un numéro de commande ainsi qu’un lien ou code de suivi.
6. Le personnel accepte ou refuse la commande avec un motif.
7. Le client consulte les changements de statut et peut demander l’aide d’un serveur (bouton appel serveur).

### 5.2 Client à emporter
1. Accède au QR Code général ou au lien public du restaurant.
2. Choisit le mode « à emporter ».
3. Renseigne les coordonnées requises par la politique du restaurant (nom, téléphone).
4. Valide la commande et suit son avancement jusqu’à ce qu’elle soit prête puis remise.

### 5.3 Équipe du restaurant
1. Reçoit une notification de nouvelle commande (sonore / visuelle).
2. Vérifie les produits, quantités, options, commentaires et disponibilité.
3. Accepte ou refuse avec un motif obligatoire.
4. Fait progresser la commande selon le flux autorisé (acceptée -> en préparation -> prête -> servie/retirée).
5. Enregistre le paiement indépendamment du statut de préparation.
6. Consulte l’historique et les indicateurs de fin de service.

---

## 6. Exigences fonctionnelles détaillées

### 6.1 Administration SaaS
- **Tableau de bord global** : restaurants inscrits, actifs, suspendus, abonnements actifs/expirés, revenus encaissés (MRR) et échéances.
- **Gestion des restaurants** : Créer, consulter, modifier, activer, suspendre et réactiver un restaurant.
- **Offres et formules** : Configurer des offres (nom, prix, périodicité, limites, fonctionnalités incluses, conditions).
- **Abonnements** : Créer et suivre les abonnements avec dates de début, échéance, période de grâce de 7 jours et historique (DEC-03). Pendant la période de grâce de sept jours, continuité complète du service avec alerte persistante (DEC-07). Après expiration de la période de grâce, blocage des nouvelles commandes clients et des opérations du point de vente, avec maintien des accès d'export (PDF/Excel) et de renouvellement pour le propriétaire (DEC-08). Préservation intégrale des données (RG-11).
- **Paiements d'abonnement** : Enregistrer les paiements d’abonnement, références de transaction, statut et justificatifs.
- **Audit administrateurs** : Journaliser les actions sensibles des administrateurs plateforme.

### 6.2 Configuration du restaurant
- Configurer nom commercial, logo, description, adresse, contacts, devise et fuseau horaire.
- Définir les horaires d’ouverture par jour et les exceptions (jours fériés ou fermeture exceptionnelle).
- Activer ou désactiver les commandes sur place et à emporter.
- Définir les règles de commande, délais estimés, politique d’annulation et champs client obligatoires.
- Configurer les notifications, taxes et frais éventuels selon les règles applicables.

### 6.3 Menus et produits
- Créer, modifier, archiver et réordonner les catégories.
- Créer des produits avec nom, description, photo, prix, catégorie, statut et temps de préparation indicatif.
- Créer des variantes (taille, format) et des groupes de suppléments avec choix requis/facultatifs et limites de sélection (min/max).
- Activer/désactiver un produit, signaler une rupture de stock et contrôler sa disponibilité selon les horaires.
- **Recalcul côté serveur impératif** : recalculer le panier côté serveur à partir des prix et options enregistrés, sans faire confiance au total envoyé par le navigateur.
- **Instantané (Snapshot) obligatoire** : préserver dans chaque ligne de commande un instantané du nom, du prix unitaire, des options et des quantités au moment de la commande.

### 6.4 QR Codes
- Générer un QR Code unique par table et un QR Code général pour les commandes à emporter.
- Associer chaque QR Code à un restaurant et, le cas échéant, à une table.
- Télécharger les QR Codes en formats adaptés à l’impression et générer un support visuel avec logo, nom du restaurant et numéro de table.
- Activer, désactiver et régénérer un QR Code. Une révocation doit rendre l’ancien code immédiatement inutilisable.
- **Sécurité des jetons** : Utiliser un jeton public aléatoire et non devinable (UUID/hash cryptographique) ; ne pas exposer les identifiants séquentiels internes.

### 6.5 Espace client et panier
- Afficher le menu public responsive sans création de compte obligatoire.
- Parcourir les catégories, rechercher un plat et afficher photos, prix, options et disponibilité.
- Ajouter, modifier et supprimer des lignes du panier, avec quantités et instructions spécifiques.
- Choisir sur place ou à emporter selon les modes activés par le restaurant.
- Afficher un récapitulatif détaillé : articles, options, sous-total, taxes/frais applicables et total.
- Valider la commande, créer une référence publique sécurisée et afficher l'écran de suivi en direct.
- Permettre une demande d’assistance liée à la table ou à la commande (appel serveur).

### 6.6 Gestion des commandes
- Afficher les commandes en temps réel ou par actualisation automatique, avec solution de repli (polling) en cas de déconnexion WebSocket.
- Filtrer par date, statut, table, type de commande et numéro.
- Accepter ou refuser une commande ; le refus doit obligatoirement comporter un motif.
- Faire évoluer les statuts de préparation selon une transition autorisée et historisée.
- Imprimer ou afficher un bon de commande lisible en cuisine (KDS / ticket cuisine).
- Gérer les annulations selon les droits et le stade de préparation ; enregistrer le motif.
- **Traitement des commandes lors de l'expiration d'abonnement** : Les commandes déjà ouvertes au moment de l'expiration de l'abonnement peuvent être menées à leur terme, notamment pour permettre la fin du service et le paiement, sans annulation automatique (DEC-09).
- Rechercher les commandes passées et consulter leur historique complet.
- Déclencher une alerte visuelle de retard lorsque le seuil configuré est atteint (45 minutes par défaut).
- Regrouper visuellement les commandes d’une table sans fusionner leurs historiques financiers.

### 6.7 Tables et assistance
- Créer, modifier et archiver les tables ; attribuer un numéro ou libellé unique au sein du restaurant.
- Visualiser les commandes en cours par table.
- Consulter les demandes d’assistance et les marquer comme prises en charge puis résolues.
- Confirmer la remise des plats si le processus du restaurant le prévoit.
- Clôturer une table selon les règles définies, sans supprimer les commandes ni les paiements historiques.

### 6.8 Employés et permissions
- Créer, modifier, désactiver et réactiver les comptes employés.
- Attribuer les rôles de référence et des permissions précises par fonctionnalité (RBAC).
- Limiter strictement chaque compte aux restaurants auxquels il est affecté.
- Révoquer immédiatement les sessions ou accès d’un utilisateur désactivé.

### 6.9 Paiements des commandes
- Permettre le suivi des paiements en espèces et des moyens électroniques retenus (Mobile Money, Carte).
- Enregistrer montant, devise, mode, date, opérateur (caissier), référence externe et statut.
- Autoriser les paiements partiels si la règle est activée ; calculer le montant payé et le solde restant dû.
- **Prévention du double encaissement** : contrôle d’état, clé d’idempotence et journal de transaction.
- Traiter les remboursements ou annulations de paiement avec permissions, motif et trace d’audit.
- Ne pas stocker les données sensibles de carte bancaire (délégation prestataire PCI-DSS).

### 6.10 Rapports et exports
- Chiffre d’affaires par jour, semaine, mois et période personnalisée.
- Nombre de commandes, panier moyen et répartition sur place / à emporter.
- Produits les plus commandés (Top ventes), annulations, refus et délais moyens de préparation.
- Rapports de paiement : montants encaissés, soldes restants, remboursements et rapprochement de caisse.
- Exporter les données autorisées en PDF ou Excel en appliquant les filtres sélectionnés.
- **Règle de distinction des métriques** : Commande créée, commande acceptée, vente réalisée et paiement encaissé sont des notions distinctes et clairement séparées dans les rapports.

### 6.11 Notifications et journalisation
- Notifier l’équipe d’une nouvelle commande et d’une demande d’assistance (sonore / push / visuelle).
- Notifier les événements importants : commande refusée, retard de préparation, abonnement proche de l’échéance ou paiement échoué.
- Configurer les canaux disponibles (in-app, e-mail, etc.).
- Conserver les événements sensibles dans un journal d’audit : connexions, modifications de droits, changements de prix, transitions de statut, paiements, remboursements et actions SaaS.

---

## 7. Règles de gestion

1. **Isolation stricte** : Chaque restaurant possède un périmètre de données isolé. Toute lecture ou écriture doit être autorisée côté serveur.
2. **Commande client sans compte** : Un client peut commander sans compte ; un jeton sécurisé permet de consulter sa commande sans exposer les données d’autres clients.
3. **Jetons QR Code révocables** : Les QR Codes publics utilisent des jetons aléatoires révocables associés à un restaurant et éventuellement une table.
4. **Instantané financier figé** : Une commande conserve un instantané du nom du produit, du prix, des variantes, des suppléments, des taxes et des quantités au moment de sa validation.
5. **Vérification de disponibilité serveur** : Un produit indisponible ne peut pas être commandé. La disponibilité est revérifiée côté serveur lors de la validation.
6. **Calculs financiers serveur** : Les prix, remises, frais et totaux sont calculés exclusivement côté serveur.
7. **Indépendance préparation / paiement** : Les statuts de préparation et de paiement sont distincts. Une commande prête peut rester impayée ; une commande payée peut être encore en préparation.
8. **Idempotence des encaissements** : Un paiement ne peut être encaissé deux fois par erreur. Chaque tentative, succès, échec, remboursement ou correction doit être traçable.
9. **Commandes à emporter sans table** : Les commandes à emporter ne nécessitent aucune table.
10. **Transitions d'états contrôlées** : Les transitions de statut sont contrôlées et validées ; toute modification manuelle sensible exige une permission et un historique.
11. **Préservation des données et cycle d'expiration d'abonnement** : Une suspension ou expiration d’abonnement ne supprime jamais les données (RG-11). Pendant la période de grâce de 7 jours (DEC-03), continuité complète du service avec alerte persistante (DEC-07). Après expiration de la période de grâce, les nouvelles commandes clients et le point de vente sont bloqués, tout en préservant les droits d'export de données et de réactivation/renouvellement par le propriétaire (DEC-08). Les commandes déjà ouvertes lors de l'expiration peuvent être menées à leur terme (service et paiement) sans annulation automatique (DEC-09).
12. **Suppression logique / Archivage** : Les suppressions d’objets métier ayant un historique financier doivent privilégier l’archivage ou le soft-delete.
13. **Fuseau horaire du restaurant** : Les dates sont stockées en UTC et présentées selon le fuseau horaire propre au restaurant.
14. **Précision monétaire** : Les montants sont stockés en unités monétaires précises (`DECIMAL(10,2)` ou entiers centimes), avec règle documentée d’arrondi et devise explicite.

---

## 8. Modèle de données conceptuel

| Entité | Attributs & Relations clés |
| :--- | :--- |
| **restaurants** | id, nom, slug, coordonnées, fuseau horaire, statut, paramètres, devise |
| **users** | id, nom, email, téléphone, mot de passe, is_super_admin, statut |
| **restaurant_user** | restaurant_id, user_id, rôle (`owner`, `manager`, `waiter`, `chef`, `staff`), permissions |
| **plans** | nom, slug, prix, périodicité, quotas (tables, produits), fonctionnalités, statut |
| **subscriptions** | restaurant_id, plan_id, début, échéance, période de grâce, statut |
| **subscription_payments** | subscription_id, montant, devise, référence, statut, date, justificatif |
| **categories** | restaurant_id, nom, ordre, is_active |
| **products** | restaurant_id, category_id, nom, description, prix, photo, disponibilité, ordre, temps_preparation |
| **product_variants / option_groups / options** | produit_id, libellé, supplément_prix, sélection min/max, obligatoire/facultatif |
| **tables** | restaurant_id, numéro/libellé, capacité, zone/emplacement, statut, is_active |
| **qr_codes** | restaurant_id, table_id nullable, token public aléatoire, type (`table`, `takeaway`), statut, date_revocation |
| **orders** | restaurant_id, table_id nullable, numéro public, mode (`dine_in`, `takeaway`), statut_preparation, statut_paiement, totaux, coordonnées client, horodatages |
| **order_items / order_item_options** | order_id, product_id, instantané nom/prix, quantité, options choisies, instructions, montant ligne |
| **payments** | order_id, montant, devise, mode (`cash`, `card`, `mobile_money`), référence externe, statut, opérateur_id, idempotency_key |
| **order_status_histories** | order_id, ancien_statut, nouveau_statut, auteur_id, motif, horodatage |
| **assistance_requests** | restaurant_id, table_id, order_id nullable, type, statut (`pending`, `acknowledged`, `resolved`), horodatages |
| **notifications** | destinataire_id, type, données, date_lecture |
| **audit_logs** | user_id, restaurant_id nullable, action, cible, horodatage, métadonnées |

---

## 9. Architecture technique cible

- **Frontend** : React + TypeScript + Inertia.js v2
- **Style UI** : Tailwind CSS avec charte Orange `#F97316`, fond `#FFF7ED`, texte `#1F2937`, dark mode compatible
- **Backend** : Laravel 11/12 (PHP 8.5+)
- **Base de données** : PostgreSQL 18 avec Row Level Security (RLS) et TenantScope
- **Temps réel** : Laravel Reverb / Broadcasting avec repli par polling automatique
- **Files d'attente** : Queue workers Laravel pour notifications, e-mails et exports
- **Fichiers** : Stockage sécurisé (photos plats, logos, justificatifs)
- **Exports** : PDF (bons de commande, tickets, QR imprimables) et Excel (rapports financiers)
- **Tests** : Pest PHP / PHPUnit avec base PostgreSQL réelle

---

## 10. Sécurité, confidentialité et conformité

- Isolation multi-tenant double barrière (Eloquent Scope + PostgreSQL RLS).
- Validation stricte côté serveur via Form Requests.
- Protection CSRF, échappement XSS, limitation de débit (Rate Limiting).
- Jetons QR Code publics aléatoires et non prédictibles (pas d'ID auto-incrémenté exposé).
- Idempotence sur les paiements pour bannir les doubles encaissements.
- Journal d’audit des actions sensibles.
- Zéro stockage de données bancaires sensibles (délégation PCI-DSS).

---

## 11. Exigences non fonctionnelles

- **Responsive & Mobile First** : Menu client optimisé smartphone ; espaces admin/POS adaptés tablettes et ordinateurs.
- **Temps réel & Résilience** : Alertes instantanées de commande avec repli automatique par polling en cas de déconnexion réseau.
- **Performance** : Temps de réponse rapides (< 300ms sur les requêtes courantes).
- **Accessibilité & Ergonomie** : Contrastes élevés, grandes cibles tactiles pour le POS en salle et le KDS en cuisine.

---

## 12. Rapports et indicateurs

- **Chiffre d’affaires réalisé** : montant des commandes validées / servies.
- **Montant encaissé** : cash et paiements électroniques réellement perçus, net des remboursements.
- **Panier moyen** : valeur moyenne des commandes.
- **Commandes acceptées vs refusées vs annulées** avec motifs.
- **Délais moyens de préparation** calculés à partir des statuts horodatés.
- **Top produits** : classement par volume et chiffre d'affaires.
- **Revenus SaaS (Plateforme)** : MRR et abonnements encaissés par l'éditeur.

---

## 13. Tests et critères de recette

| ID | Scénario de recette | Résultat attendu |
| :--- | :--- | :--- |
| **REC-01** | Un restaurant crée un produit et le publie. | Le produit apparaît dans son menu public avec le prix défini. |
| **REC-02** | Un client scanne le QR d’une table. | Le restaurant et la table sont identifiés sans révéler d’identifiant interne sensible. |
| **REC-03** | Un client valide un panier avec options. | Le serveur recalcule le total et enregistre un instantané complet. |
| **REC-04** | Le prix du produit change après une commande. | La commande existante conserve son prix historique (snapshot). |
| **REC-05** | Un produit est déclaré indisponible avant validation. | La commande est refusée ou le client doit actualiser son panier. |
| **REC-06** | Un restaurant tente d’accéder à une commande d’un autre restaurant. | L’accès est refusé (403/404), y compris via modification d’URL ou d’identifiant. |
| **REC-07** | Une commande est prête mais non payée. | Les statuts de préparation et de paiement restent indépendants. |
| **REC-08** | Une même opération de paiement est soumise deux fois. | Aucun double encaissement n’est créé (idempotence). |
| **REC-09** | Un abonnement arrive à échéance. | La politique de grâce/blocage s’applique sans supprimer les données. |
| **REC-10** | Un QR Code est révoqué. | L’ancien code n’ouvre plus le contexte protégé. |
| **REC-11** | Un employé sans permission tente un remboursement. | L’action est refusée et journalisée. |
| **REC-12** | Une sauvegarde est restaurée en environnement de test. | Les données et relations critiques sont restaurées selon le RPO/RTO convenu. |
| **REC-13** | Le canal temps réel est indisponible. | Le mécanisme de repli (polling) permet de récupérer les nouvelles commandes. |
| **REC-14** | Un rapport est exporté en PDF ou Excel. | Les filtres, montants et droits correspondent aux données affichées. |

---

## 14. Déploiement, exploitation et sauvegardes

- Environnements distincts (Local, Staging, Production).
- Migrations réversibles et documentées.
- Workers de queue et scheduler Laravel surveillés.
- Sauvegardes automatisées de PostgreSQL avec tests réguliers de restauration.

---

## 15. Découpage prévisionnel officiel

- **Phase 1 — Cadrage & Architecture** *(Achevée)* : Cahier des charges, architecture multi-tenant, PostgreSQL RLS.
- **Phase 2 — Design System & Socle SaaS** *(Validation technique accordée sous réserve de la consignation des arbitrages et des vérifications finales. Ne vaut pas autorisation de commencer la Phase 3)* : React + Inertia + TS, identité Orange `#F97316`, gestion des restaurants, plans et abonnements (DEC-03, DEC-07, DEC-08, DEC-09).
- **Phase 3 — Menu, Produits, Tables & QR Codes** : Catégories, produits, variantes/options, disponibilité, gestion des tables et génération/révocation des QR Codes sécurisés.
- **Phase 4 — Prise de Commande, Espace Client & Cuisine (KDS)** : Menu public, panier, validation serveur, suivi temps réel, écran cuisine, assistance serveur.
- **Phase 5 — Caisse (POS), Paiements & Rapports Financiers** : Encaissements multi-modes, anti-double encaissement, clôtures de caisse, exports PDF/Excel, tableaux de bord.
- **Phase 6 — Qualité, Sécurité & Déploiement** : Tests E2E complets, audit de sécurité OWASP, procédures de sauvegarde et mise en production.

---

## 16. Risques et décisions à arbitrer avec le client

1. **Moyens de paiement électroniques** : Sélection du/des prestataires locaux (Wave, Orange Money, MTN Moov Money, Stripe/Paystack).
2. **Politique d'abonnement expiré [ARBITRÉ — DEC-03, DEC-07, DEC-08, DEC-09]** : Durée de grâce fixée à 7 jours avec continuité complète du service et alerte persistante (DEC-07) ; blocage post-grâce des nouvelles commandes et du POS avec maintien des exports et du renouvellement (DEC-08) ; autorisation d'achever les commandes déjà ouvertes sans annulation automatique (DEC-09) ; préservation intégrale des données.
3. **Paiement client** : Définir si le paiement en ligne par le client est obligatoire avant validation de la commande ou si le paiement se fait à table/à la caisse (paramétrable par restaurant).
4. **Politique d'annulation client** : Fenêtre de temps accordée au client pour annuler avant que la cuisine n'accepte la commande.
5. **Gestion fine des stocks** : Gestion par rupture manuelle simple ou décrémentation automatique d'unités d'ingrédients.
6. **Impression des tickets** : Formats cibles (thermique 80mm ESC/POS, réseau ou impression PDF standard).

---

## 17. Livrables attendus

- Cahier des charges fonctionnel et technique validé.
- Maquettes UI/UX desktop et mobile (Orange `#F97316`, fond `#FFF7ED`, texte `#1F2937`).
- Modèle logique de données et migrations PostgreSQL avec RLS.
- Code source complet avec typage TypeScript strict et FormRequests.
- Matrice de traçabilité et suite de tests automatisés.
- Documentation d'exploitation et manuels utilisateurs.

---

## Annexe A — Statuts recommandés

### A.1 Statuts de préparation d'une commande
- `pending` : Commande soumise, en attente de décision du restaurant.
- `accepted` : Commande acceptée et en attente de préparation.
- `preparing` : Préparation en cours en cuisine.
- `ready` : Commande prête à être servie ou retirée.
- `served` : Commande servie sur place (si étape activée).
- `completed` : Commande terminée et clôturée.
- `rejected` : Commande refusée avec motif obligatoire.
- `cancelled` : Commande annulée avec motif et traçabilité.

### A.2 Statuts de paiement
- `unpaid` : Aucun paiement confirmé.
- `partially_paid` : Une partie du montant a été encaissée (si paiements partiels activés).
- `paid` : Montant dû intégralement encaissé.
- `refund_pending` : Remboursement en cours de traitement.
- `partially_refunded` : Une partie des montants encaissés a été remboursée.
- `refunded` : Montant remboursable intégralement remboursé.
- `failed` : Tentative de paiement échouée (la commande reste active).

---

## Annexe B — Identité visuelle officielle

- **Orange principal** : `#F97316` (Boutons d'action, accents, statuts en cours)
- **Orange foncé** : `#C2410C` (Survols, bordures actives)
- **Fond clair** : `#FFF7ED` (Arrière-plan doux d'accueil et cartes)
- **Texte principal** : `#1F2937` (Gris très foncé pour un contraste et une lisibilité maximale)
- **Police** : Aptos ou équivalent sans-serif moderne (Inter / Plus Jakarta Sans)
- **Principes** : Mobile-first pour les clients, interface tactile POS/KDS, contrastes élevés.
