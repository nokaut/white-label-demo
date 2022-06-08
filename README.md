Nokaut.pl Demo White Label (PHP)
==============================

[![Build Status](https://travis-ci.org/nokaut/white-label-demo.svg?branch=master)](https://travis-ci.org/nokaut/white-label-demo.svg?branch=master)

Demo serwisu porównywarki cen wykonane na frameworku [Symfony](http://symfony.com/)

Status
------

Demo serwisu porównywarki cen. Zawiera podstawowe funkcjonalności do uruchomienia własnego serwisu.

Wymagania
---------

* PHP 7.2.5+
* Dostęp do Search API (klucz OAuth) - kontakt z Nokaut.pl
* Opcjonalnie Memcached (wymagana wtyczka php-memcached)

Instalacja
----------
Uruchamiamy konsolę i przechodzimy do katalogu, w którym ma być projekt np. `/web/porownywarka` i pobieramy projekt:

    git clone git@github.com:nokaut/white-label-demo.git .

Kropka na końcu jest ważna!

Po pobraniu wykonujemy instalację projektu. Rekomendowaną formą instalacji jest skorzystanie z [Composer'a](http://getcomposer.org/).
Najpierw należy zainstalować Composer'a - [szczegóły tutaj](https://getcomposer.org/download/) 

Następnie instalujemy pakiety Composer'em:

    php composer.phar install

Podczas instalacji program poprosi nas o podanie parametrów. Zostawiamy domyślne (naciskając Enter) dla wszystkich parametrów oprócz:

 - api_token: - tu wprowadzamy token, który dostaniemy od Nokaut.pl
 - cache_enabled: - jeśli mamy zainstalowany memcached i chcemy używać cache wprowadzamy `true` w innym przyadku wprowadzamy `false`
 - memcache_url: - jeśli w poprzedni parametrze wprowadziliśmy `false`, naciskamy enter, jeśli `true`, musimy podać adres serwera memcached, jeśli memcached jest na tym samym serwerze co serwis, postawiamy domyślą wartość `localhost`
 - memcache_port: - jeśli w parametrze `cache_enabled` wprowadziliśmy `false`, naciskamy enter, jeśli `true`, musmy podać port serwera memcached, domyślnie memcached jest na porcie 11211
 - product_mode: - tryb widoku produktu, są dostępne dwie opcję `modal`, `page`
    - `page` - ustawia produkt z ofertami jako osobną stronę, która będzie indeksowana przez wyszukiwarki takie jak Google
    - `modal` - produkt i jego oferty prezentowany jest w okienku typu modal, przez co nie jest indeksowany przez wyszukiwarki
 - domain: - domena, pod którą będzie znajdowała się strona, format: http://moj-serwis.pl/
 - site_name: - nazwa serwisu, będzie się wyświetlać między innymi na górze oraz w stopce strony
 - google_analytics_id: - identyfikator śledzenia dla Google Analytics (numer który najcześciej zaczyna się od _UA-_ np: _UA-1234556-1_, dostępny w zakładce Administracja w analytics.google.com dla danego projektu)
 - categories: - parametr odpowiadający za tematykę strony, wybieramy w nim ID-ki kategorii które mają się znaleźć w serwisie, np:

         'Kategorie I': #ta nazwa pojawi się w menu głównym
             - id kategorii 1
             - id kategorii 2
             ...
         'Kategorie II':
             - id kategorii 3
             - id kategorii 4
         ....
    - Możemy również w polu `categories` podać `null` co oznacza że będzie dostępny cały katalog produktów.


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

Dostaniemy informację `Server running on http://127.0.0.1:8000` i teraz możemy przejść do przeglądarki wpisując w adres `http://localhost:8000/` - ujrzymy nasz serwis.

Bardzo ważna rzecz: css, JavaScript i obrazki trzymane są w katalogu `src/WL/AppBundle/Resources/public/` po każdej zmianie w tych plikach lub dodaniu nowego należy uruchomić polecenie:

     php app/console asset:install

aby zmiany naniosły się na katalog publiczny projektu.


Uruchamiane aplikacji w Dockerze
--------------------------------

### Set up

```bash
docker-compose build
```

### Composer install

```bash
docker-compose run app install
```

lub w razie problemów wejście do kontenera i instalacja (composer może zapytać do token do githuba)

```bash
docker-compose run app shell
composer install
...
exit
```

## Uruchomienie

```bash
docker-compose up
```

Aplikacja dostępna będzie w przeglądarce (np. Chrome) pod adresem: http://localhost:8000/


FAQ
---

**Wykonałem wszystkie polecenia na swoim komputerze i strona działa, ale gdy wrzuciłem na serwer cały katalog strona przestała działać**

Należy wykonać polecenia:

     php app/console cache:clear --env=prod

Jeśli na twoim serwerze nie masz możliwości zalogowania się do konsoli i wykonania powyższych poleceń usuń zawartość katalogów, ale same katalogi pozostaw:

     app/cache/
     app/logs/

Sprawdź również czy powyższe katalogi mają prawo zapisu z serwera www.

