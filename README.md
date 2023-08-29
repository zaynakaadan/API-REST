# API-REST
Project 7 - API REST BileMo - OCR


[![Codacy Badge](https://app.codacy.com/project/badge/Grade/a85ccacff16b49a39f59f206f124a0cd)](https://app.codacy.com/gh/zaynakaadan/API-REST/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
<h3>Documentation en ligne :</h3>
<p>Une interface pour documenter l'API et teser les différentes méthodes a été réalisée à l'aide de NelmioApiDocBundle.</p>

<h3>Documentation</h3>
<p>L'ensemble du code source a été commenté. L'utilsation de PhpDocBlocker a permis de générer une documentation claire et précise.</p>

<h3>Diagrammes UML</h3>
Les schémas UML se situent dans le dossier diagrams_UML à la racine du projet:
<ul>
  <li>Diagramme de classe</li>
  <li>Diagramme de cas d'utilsation</li>
  <li>Diagramme de séquence</li>
  <li>MPD</li>
</ul>  
Les fonctoinnalités décrites dans les diagrammes concernent les clients, les utilisateurs et les téléphones.

<h3>Langage de programmation</h3>

<ul>
</ul>
<li>L'API REST BileMo a été développé en PHP via le framework Symfony 6.3</li>
<li>L'utilisation de librairy telles que FosRestBundle, JMSSerializer et Hateoas ont été utilisées pour gérer l'ensemble des contraintes associées à la création d'une API REST.

<hr>
<h2>Installation</h2>
<h3>Environnement nécessaire</h3>
<ul>
  <li>Symfony 6.3.*</li>
  <li>PHP 8.2.*</li>
  <li>MySql 8</li>
</ul>
<h3>Suivre les étapes suivantes :</h3>
<ul>
  <li><b>Etape 1.1 :</b> Cloner le repository suivant depuis votre terminal :</li>
  <pre>
  <code>git clone https://github.com/zaynakaadan/API-REST.git</code></pre>     
  
   <li><b>Etape 1.2 :</b> Executer la commande suivante :</li>
  <pre>
  <code>composer install</code></pre>     
  
    <li><b>Etape 1.3* :</b> Si besoins, ajouter le package symfony/apache-pack (en fonction de votre environnement de déploiement) :</li>
  <pre>
  <code>composer require symfony/apache-pack</code></pre>     
  <li><b>Etape 2 :</b> Editer le fichier .env </li>
    - pour renseigner vos paramètres de connexion à votre base de donnée dans la variable DATABASE_URL
  <li><b>Etape 3 :</b> Démarrer votre environnement local (Par exemple : Wamp Server)</li>
  <li><b>Etape 4 :</b> Exécuter les commandes symfony suivantes depuis votre terminal</li>
  <pre><code>
    symfony console doctrine:database:create (ou php bin/console d:d:c si vous n'avez pas installé le client symfony)<br/>
    symfony console doctrine:migrations:migrate<br/>
    symfony console doctrine:fictures:load  
  </code></pre>
  <li><b>Etape 5.1 :</b> Générer vos clés pour l'utilisation de JWT Token</li>
  <pre><code>
    $ mkdir -p config/jwt
    $ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    $ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
  </code></pre>
  <li><b>Etape 5.2 :</b> Renseigner vos paramètres de configuration dans votre ficher .env</li>
  <pre><code>
    ###> lexik/jwt-authentication-bundle ###
    JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
    JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
    JWT_PASSPHRASE=VotrePassePhrase
    ###< lexik/jwt-authentication-bundle ###
  </code></pre>
  <li><b>Etape 5.3 :</b> Générer un Token pour pouvoir tester l'API </li>
  <pre><code>
    $ Symfony console lexik:jwt:generate-token client@bilemo.com
    
    => eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MDcwNzcwMT..._MsImV4cCI6MTYzODYx
  </code></pre>
</ul>
  
<h3>Vous êtes fin prêt pour tester votre API!</h3>
<p>Pour afficher la doucmentation en ligne et tester l'APIrendez-vous à l'adresse suivante votre navigateur : <em>http://yourAdress.domain.fr/doc/api</em></p>
