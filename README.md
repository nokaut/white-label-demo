Nokaut.pl Demo White Label (PHP)
==============================

[![Build Status](https://travis-ci.org/nokaut/white-label-demo.svg?branch=master)](https://travis-ci.org/nokaut/white-label-demo.svg?branch=master)

Demo serwisu porównywarki cen wykonana na frameworku [Symfony2](http://symfony.com/)

Status
------

Demo serwisu porównywarki cen. Zwiera podstawowe funkcjonalności do uruchomienia własnego serwisu.

Wymagania
---------

* PHP 5.4+
* dostęp do Search API (klucz OAuth) + odblokowanie IP serwera - kontakt z Nokaut.pl
* Opcjonalnie Memcache (wymagana wtyczka php-memcache)

Instalacja
----------
Uruchamiamy konsole i przechodzimy do katalogu w który ma być projekt np. `/web/porownywarka` i pobieramy projekt:

    git clone git@github.com:nokaut/white-label-demo.git .

Kropka na końcu jest ważna!

Po pobraniu wykonujemy instalacje projektu. Rekomendowaną formą instalacji jest skorzystanie z [Composer'a](http://getcomposer.org/).
Najpierw należy zainstalować Composer'a:

    curl -sS https://getcomposer.org/installer | php

Następnie instalujemy pakiety Composer'em:

    php composer.phar install

Podczas instalacji program poprosi nas o podanie parametrów. Zostawiamy domyślne (naciskając Enter) dla wszystkich parametrów oprócz:

 - api_token: - tu wprowadzamy token który dostaniemy od Noakut.pl
 - cache_enabled: - jeśli mamy zainstalowany memcache i chcemy używać cache wprowadzamy `true` w innym przyadku wprowadzamy `false`
 - memcache_url: - jeśli w poprzedni parametrze wprowadziliśmy `false` naciskamy enter jeśli `true` musimy podać adres serwera memcache, jeśli memcache jest na tym samym serwerze co serwis, postawiamy domyślą wartość `localhost`
 - memcache_port: - jeśli w parametrze `cache_enabled` wprowadziliśmy `false` naciskamy enter jeśli `true` musmy podać port serwera memcache, domyślnie memcache jest na porcie 11211
 - categories: - parametr odpowiadający za tematykę strony, wybieramy w nim ID-ki kategorii które mają się znaleźć w serwisie, domyślnie są wszystkie kategorie przykładowo podzielone na 3 zakładki; schemat:

         'Kategorie I': #ta nazwa pojawi się w menu głównym
             - id kategorii 1
             - id kategorii 2
             ...
         'Kategorie II':
             - id kategorii 3
             - id kategorii 4
         ....


Pramatry można w każdej chwili zmienić w pliku  **app/config/parameters.yml**

Po uzupełnieniu parametrów wykonujemy dwa ostatnie polecenia **(to polecenie należy zawsze wykonać po aktualizacji powyższego pliku z parametrami)**.

     php app/console cache:clear --env=prod
     php app/console asset:install --env=prod

 Następujące katalogi muszą posiadać uprawnienia zapisu z poziomu skryptu PHP

     app/cache/
     app/logs/

 Domena musi być ustawiona na katalog:

     <ścieżka do katalgu z projektem>/web/

Uruchamiane serwera dewelopersko - czyli do wykonywania zmian na nim
--------------------------------------------------------------------

Po zainstalowaniu projektu, możemy uruchomić go w trybie do pracy, wchodzimy do katalogu gdzie jest projekt i wykonujemy polecenie:

    php app/console server:run

dostaniemy informację `Server running on http://127.0.0.1:8000` i teraz możemy przejść do przeglądarki wpisując w adres `http://localhost:8000/` ujrzymy nasz serwis.

Bardzo ważna rzecz: css, JavaScript i obrazki trzymane są w katalogu `src/WL/AppBundle/Resources/public/` po każdej zmianie w tych plikach lub dodaniu nowego należy uruchomić polecenie:

     php app/console asset:install

aby zmiany naniosły się na katalog główny z projektem.


FAQ
---

**Wykonałem wszystkie polecenia strona działa, ale gdy wrzuciłem na serwer cały katalog strona przestała działać**

Należy wykonać polecenia:

     php app/console cache:clear --env=prod

Jeśli na twoim serwerze nie masz możliwości zalogowania się do konsoli i wykonania powyższych poleceń usuń zawartość katalogów, ale same katalogi pozostaw:

     app/cache/
     app/logs/

