# Pour créer un module
```
php artisan make:module NomDuModule
```

# Pour créer un LogActivity dans chaque action du user dans le controller
```
LogActivity("Création d'un article", $data, $article)
```

# Pour envoyer une notification en SMS dans une method du controller
```
SendMessageEvent::dispatch($telephone, $message);
```

# Créer une migration 
```
php artisan make:migration create_nomtable_table
```

# Créer un model 
```
php artisan make:model NomModel --path=app/Modules/NomModule/Models
```

# Créer un controller 
```
php artisan make:contoller NomController --path=app/Modules/NomModule/Contollers
```

# Créer un request 
```
php artisan make:request NomRequest --path=app/Modules/NomModule/Requests
```

# Créer une resource 
```
php artisan make:resource NomResource --path=app/Modules/NomModule/Resources
```

## COMMANDE DE RACOURSIE

# Créer un model avec sa migration
```
php artisan make:model NomModel -m
```

# Créer un model avec sa migration et controller
```
php artisan make:model NomModel -mc
```
