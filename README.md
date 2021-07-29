# Prete-moi-tes-mangas
[![forthebadge](http://forthebadge.com/images/badges/built-with-love.svg)](http://forthebadge.com)  [![forthebadge](http://forthebadge.com/images/badges/powered-by-electricity.svg)](http://forthebadge.com)
## Description
Ceci est un projet de fin d'études dans le cadre d'une formation intensive de Développeur Web Fullstack

### MVP
Il s'agit d'un projet de prêt de mangas entre particuliers, avec différentes fonctionalités (pour l'instant c'est un MVP) :
- Possibilité de s'inscrire ou se connecter, de modifier son profil utilisateur et sa collection de mangas (les mangas et les tomes associés)
- Possibilité de faire une recherche par code postal, sur un rayon de 30kms et de voir les mangas et tomes disponibles
- Possibilité de contacter un utilisateur via un système de chat en realtime
- Possibilité de modifier la disponibilité de ses tomes selon les prêts validés
- Possibilité de contacter un admin via un formulaire de contact

Back office (exclusivement en Symfony/EasyAdmin3) :
- Possibilité de gérer les mangas :  ajout des nouveaux mangas et les tomes associés, supression de mangas
- Possibilité de gérer les utilisateurs : création, supression, édition
- Possibilité de gérer les messages reçus via le formulaire de contact : lecture, réponse, création de message

Envoi d'email :
Notre back étant hébergé sur O2Switch, nous avons pu mettre en place des envois d'emails, mis en forme avec Inky pour : la création de compte, la réception d'un message sur le chat, la réception d'un message d'un admin.
### V2
Dans une V2, nous envisageons de :
- Pouvoir adapter le rayon de recherche selon la saisie utilisateur
- Pouvoir faire une recherche par manga
- Pouvoir gérer les transactions, et automatiquement gérer la disponibilité des tomes selon la transaction et sa durée
- Pouvoir visualiser les rendez-vous passés et à venir
- Pouvoir charger une photo de profil


## Stack:
la partie back est en Symfony, et le front est en React. Ce repo gère uniquement la partie Back.
Le real time du chat est géré via un serveur Node et socket.io.
La communication fonctionne donc en mode API, avec sécurisation par token JWT. 


## Back
Pour la partie back, nous avons utilisé : PHP 7 et Symfony 5
Le back peut être décomposé en deux parties : 
- les controllers d'API, qui renvoient les données nécessaires au Front, en format JSON
- les controllers Admin, qui gèrent la logique du backoffice, qui est entièrement créé en Symfony avec le bundle EasyAdmin3.
- La base de données de manga a été remplie via des appels à une API permettant de récupérer toutes les informations d'un manga à partir de son titre, dont l'url de l'image, hébergée sur un CDN. Les tomes sont créés à la volée selon le nombre de tomes, information renvoyée par l'API

