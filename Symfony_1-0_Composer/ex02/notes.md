## LTS version of PHPUnit compatible with PHP 8.2
This development requirement described in the subject is configured in the composer.json file as such:

``` json
"require-dev": {
        "phpunit/phpunit": "9.5.*" 
    }
```

## Commande pour update si besoin

``` bash
compose update -W --no-audit
```
[-W pour autoriser l'update malgre de possibles problemes de compatibilite, selon les infos su composer.json]

## Pour ignorer certains conseils de Composer
Ajouter dans la partie "config" di composer.json le block suivant :

```json
"audit": {
            "ignore": [
                "[element_to_ignore]"
            ]
        }
```
