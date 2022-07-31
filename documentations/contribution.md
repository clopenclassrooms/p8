# Comment contribuer au projet
## Téléchargement du code source
```bash
git clone https://github.com/clopenclassrooms/p8.git
```

## Installation
Voir le [Readme](https://github.com/clopenclassrooms/p8/blob/master/Readme.md) du projet

## Contribuer au projet
### La todolist
Vous pouvez consulter la liste des tâches à réaliser dans les [Issues](https://github.com/clopenclassrooms/p8/issues)

### Ajout et modification de code

Avant d'ajouter ou modifier le code du projet, veuillez à créer et vous placer sur une nouvelle branche via la commande : 
``` bash
git branch <Nom de la nouvelle branche>
git checkout <Nom de la nouvelle branche>
```

Pour uploader le code de la branche sur le dépot git :
```bash
git add <fichiers/répertoires modifiés >
git commit -m "<un commentaire>"
git push --set-upstream origin <Nom de la nouvelle branche>
```

### Demande d'intégration du code de la nouvelle branche
Avant de faire une demande d'intégration de votre branche au projet, merci de vérifier que votre code n'entraine pas de nouveau problème au projet : 
``` bash 
php bin/phpunit
```

Merci de vérifier la qualité de votre code via l'outil [Codacy](https://www.codacy.com/)

Une fois réalisé, vous pouvez faire une demande d'intégration de code via un [pull request](https://github.com/clopenclassrooms/p8/pulls)
