# Nokaut.pl Demo White Label (PHP)
Demo serwisu porównywarki cen wykonane na frameworku [Symfony](http://symfony.com/). Zawiera podstawowe funkcjonalności do uruchomienia własnego serwisu.

# Wymagania
* PHP 8.0+
* Dostęp do Search API (klucz OAuth) - kontakt z Nokaut.pl
* Opcjonalnie Memcached (wymagana wtyczka php-memcached)

# Pobranie aplikacji
Uruchamiamy konsolę i przechodzimy do katalogu, w którym chcemy umieścić projekt i pobieramy go z repozytorium (kropka na końcu jest istotna):

    git clone git@github.com:nokaut/white-label-demo.git .

# Zmienne środowiskowe

Tworzymy plik `.env` poleceniem

    cp .env.dist .env

W nim ustawiamy środowisko, w którym będzie działał serwis, domyślnie jest to `dev`, jeśli chcemy uruchomić serwis w
produkcyjnie, zmieniamy wartość na `prod`:

    APP_ENV=prod

# Uruchomienie aplikacji

## Instalacja zależności i konfiguracja
Wykonujemy instalację projektu [Composer'em](https://getcomposer.org/download/):

    composer install

Podczas instalacji program poprosi nas o podanie parametrów. Zostawiamy domyślne (naciskając Enter) dla wszystkich
parametrów oprócz:

- api_token: - tu wprowadzamy token, który dostaniemy od Nokaut.pl
- cache_enabled: - jeśli mamy zainstalowany memcached i chcemy używać cache wprowadzamy `true` w innym przyadku
  wprowadzamy `false`
- memcache_url: - jeśli w poprzedni parametrze wprowadziliśmy `false`, naciskamy enter, jeśli `true`, musimy podać adres
  serwera memcached, jeśli memcached jest na tym samym serwerze co serwis, postawiamy domyślą wartość `localhost`
- memcache_port: - jeśli w parametrze `cache_enabled` wprowadziliśmy `false`, naciskamy enter, jeśli `true`, musmy podać
  port serwera memcached, domyślnie memcached jest na porcie 11211
- product_mode: - tryb widoku produktu, są dostępne dwie opcję `modal`, `page`
    - `page` - ustawia produkt z ofertami jako osobną stronę, która będzie indeksowana przez wyszukiwarki takie jak
      Google
    - `modal` - produkt i jego oferty prezentowany jest w okienku typu modal, przez co nie jest indeksowany przez
      wyszukiwarki
- domain: - domena, pod którą będzie znajdowała się strona, format: http://moj-serwis.pl/
- site_name: - nazwa serwisu, będzie się wyświetlać między innymi na górze oraz w stopce strony
- google_analytics_id: - identyfikator śledzenia dla Google Analytics (numer, który najcześciej zaczyna się od _UA-_ np:
  _UA-1234556-1_, dostępny w zakładce Administracja w analytics.google.com dla danego projektu)
- categories: - parametr odpowiadający za tematykę strony, wybieramy w nim ID-ki kategorii, które mają się znaleźć w
  serwisie, np:

        'Kategorie I': #ta nazwa pojawi się w menu głównym
            - id kategorii 1
            - id kategorii 2
            ...
        'Kategorie II':
            - id kategorii 3
            - id kategorii 4
        ....

Parametry można w każdej chwili zmienić w pliku **config/packages/parameters.yml**

Po uzupełnieniu lub zmianie parametrów wykonujemy polecenia:

     php bin/console cache:clear --env=prod
     php bin/console assets:install --symlink --relative public --env=prod

 Następujące katalogi muszą mieć uprawnienia zapisu z poziomu skryptu PHP

     var/cache/
     var/log/

 Domena w konfiguracji serwera WWW musi być ustawiona na katalog:

     <ścieżka do katalgu z projektem>/public/

## Uruchomienie deweloperskiego serwera WWW

Po zainstalowaniu projektu możemy uruchomić go lokalnie w trybie deweloperskim, wchodzimy do katalogu gdzie jest projekt i wykonujemy polecenie:

    symfony serve

Dostaniemy informację `Server running on http://127.0.0.1:8000` i teraz możemy przejść do przeglądarki wpisując w adres `http://localhost:8000/` - ujrzymy nasz serwis.

Uwaga: pliki CSS, JavaScript i obrazki trzymane są w katalogu `public/`.

# Uruchomienie aplikacji w Dockerze

## Przygotowanie środowiska i aplikacji

### Zbudowanie obrazów usług

```bash
docker-compose build
```

## Utworzenie i uruchomienie kontenerów

```bash
docker-compose up
```

### Instalacja zależności i konfiguracja

W nowym okienku konsoli przechodzimy do katalogu aplikacji, wchodzimy na kontener aplikacji.
```bash
docker-compose exec php bash
```

Konfigurujemy i instalujemy zależności.
```bash
composer install
```

Aplikacja dostępna będzie w przeglądarce (np. Chrome) pod adresem: http://127.0.0.1:8000

## Testy

Aby uruchomić testy, należy w katalogu aplikacji wejść do kontenera poprzez polecenie:

    docker-compose exec php bash

I wykonać polecenie:

    bin/phpunit

Możesz wykonać testy jednostkowe dla konkretnego pliku, np.:

    bin/phpunit tests/Controller/DefaultControllerTest.php

Albo też wyłączyć grupę testów, np.:

    bin/phpunit --exclude-group=integration

FAQ
---

**Wykonałem wszystkie polecenia na swoim komputerze i strona działa, ale gdy wrzuciłem na serwer cały katalog strona
przestała działać**

Należy wykonać polecenia:

     php bin/console cache:clear --env=prod

Jeśli na twoim serwerze nie masz możliwości zalogowania się do konsoli i wykonania powyższych poleceń usuń zawartość katalogów, ale same katalogi pozostaw:

     var/cache/
     var/log/

Sprawdź również, czy powyższe katalogi mają prawo zapisu z serwera www.

