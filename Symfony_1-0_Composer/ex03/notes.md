# Notes

Dans Task1, on installe la configuration du fichier ```composer.lock``` d'abord avec ```composer install```, puis en faisant
```composer update``` on lit le fichier ```composer.json``` qui met a jour les versions des paquets decrits dans le fichier.

Dans Task2, le fichier ```composer.lock``` a ete mis a jour. Donc quand on lance la commande ```composer install```, on
installe des versions des paquets deja upgrades dans task1 via ce fichier.
Avec ensuite ```composer upgrade```, on tente de nouveau de mettre a jour si possible en passant cette fois de nouveau par composer.json. SI possible, le ```composer.lock``` est alors remis a jour.

## composer install

Utilise composer.lock → versions fixées et identiques

## composer update

Lit composer.json → cherche les nouvelles versions compatibles → actualise composer.lock

- ✅ Task1: install (lock) → update (json)
- ✅ Task2: install (lock mis à jour de task1) → update (tente nouvelles versions)
