# Commandes Symfony

## Creer un nouveau projet

- **via une commande Composer**

```bash
composer create-project [app name] "^versionLTS"
```

- **via une commande Symfony**

``` bash
symfony new [app name] --version=lts
```

- **demarrer le serveur local en arriere plan**

``` bash
symfony server:start -d
```

- **stopper le serveur local**

``` bash
symfony server:stop
```

## Les Templates Twig

### Heritage

Toutes les templates qui heritent de base.html.twig doivent commencer par :

```html
{% extends "base.html.twig" %}
```

Les templates remplacent ensuite les "blocks" du fichier base...
