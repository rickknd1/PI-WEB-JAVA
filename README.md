# PI‑WEB‑JAVA · SYNKYLINKY 🌍  
![Build](https://img.shields.io/badge/build-passing-brightgreen) ![Symfony](https://img.shields.io/badge/symfony-7.x-black) ![License](https://img.shields.io/badge/license-academic-blue)

> **Esprit School of Engineering** – 3ᵉ année (2024‑2025)  
> Projet de fin de module **PI‑WEB‑JAVA** : créer une plateforme sociale immersive pour la **démocratisation de la culture**.

---

## 📑 Sommaire
- [🎯 Description](#-description)
- [✨ Fonctionnalités](#-fonctionnalités)
  - [👥 Module Utilisateur](#module-utilisateur)
  - [🌐 Module Communauté](#module-communauté)
  - [📰 Module Fil d’Actualité](#module-fil-dactualité)
  - [🏅 Module Abonnement & Gamification](#module-abonnement--gamification)
  - [🏛️ Module Ville & Lieux Culturels](#module-ville--lieux-culturels)
- [🛠️ Technologies](#-technologies)
- [📂 Arborescence](#-arborescence)
- [⚙️ Installation](#️-installation)
- [🚀 Utilisation](#-utilisation)


---

## 🎯 Description
**Cultural Hub** est un réseau social dédié à la découverte et à la promotion du patrimoine culturel.  
Il propose une **expérience immersive** combinant :
- Découverte de **lieux culturels** (musées, sites historiques, galeries…) via cartes interactives, visites 360°, AR, météo et infos transport en temps réel.
- Interaction sociale complète : communautés, fil d’actualité, messagerie, événements, quêtes gamifiées.
- Système d’**abonnement premium** offrant des avantages et un **store** où échanger des points gagnés.

---

## ✨ Fonctionnalités

### Module Utilisateur
| Fonction | Détails |
| --- | --- |
| **Rôles** | `ADMIN`, `USER`, `MODERATOR` (hérité) |
| Authentification | Inscription, connexion, **MFA** (TOTP), réinitialisation par mail |
| Profil avancé | Avatar, bio riche, portefeuille de badges, préférences de confidentialité |
| Social graph | Amis, abonnements, blocage des utilisateurs |
| Analytics Admin | Dashboard de statistiques (utilisateurs actifs, signalements, churn) |
| RGPD | Export/effacement de données personnelles |

### Module Communauté
| Fonction | Détails |
| --- | --- |
| Groupes par intérêt | Musique, art, architecture, cinéma, etc. |
| Publications enrichies | Texte, images, vidéos, sondages, hashtags |
| Chat temps réel | WebSocket + emojis + pièces jointes |
| Événements de groupe | Organisation, RSVP, intégration Google Calendar |
| Modération | Règles personnalisées, file de posts à approuver |
| Recommandations IA | Suggestion de communautés selon profil et activité |

### Module Fil d’Actualité
| Fonction | Détails |
| --- | --- |
| Algorithme hybride | Score basé sur pertinence, récence, affinités |
| Réactions multiples | 👍 ❤️ 😂 😮 😢 😡 |
| Commentaires threadés | Réponses illimitées, @mentions |
| Partage externe | Cartes d’aperçu pour liens (Open Graph) |
| Mode « Tendance » | Topics chauds, #hashtags populaires |
| Infinite scroll | Chargement paresseux, pré‑fetch adaptatif |

### Module Abonnement & Gamification
| Fonction | Détails |
| --- | --- |
| **Tiers** | Free, **Explorer**, **Patron** |
| Avantages Premium | Suppression pubs, badges exclusifs, accès bêta |
| Quêtes quotidiennes | Poster, visiter un lieu, inviter un ami… |
| Niveaux & XP | Progression visuelle, barres d’XP |
| **Leaderboard** | Top 100 hebdomadaire, filtres par ville/groupe |
| Boutique de récompenses | Réductions partenaires, billets d’événements |

### Module Ville & Lieux Culturels
| Fonction | Détails |
| --- | --- |
| Carte interactive | Leaflet + clustering des POI |
| Visites virtuelles | Panoramas 360°, vidéos immersives |
| AR Mode | Sur mobile, surimpression d’informations |
| Météo & transport | API OpenWeather + GTFS |
| Itinéraires personnalisés | Plan d’une journée culturelle, partageable |
| Billetterie | Intégration Stripe + QR Code pour l’entrée |

---

## 🛠️ Technologies
| Couche | Stack |
| --- | --- |
| **Frontend** | Twig, Bootstrap 5, Alpine.js |
| **Backend** | Symfony 7 (PHP 8.3), Java JDK 24 (modules micro‑services) |
| **DB** | MySQL 8, Doctrine ORM |
| **Realtime** | Mercure, WebSocket |
| **Tests** | PHPUnit, PHPStan, Psalm |
| **CI/CD** | GitHub Actions, Docker (en staging) |

---

## 📂 Arborescence
