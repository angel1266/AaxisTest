# AaxisTest

Para correr el proyecto  de forma local debe cumplir con los siguientes requisitos:  
* instalar base de datos postgresql.  
* php version >= 8.1.  
1. **Clonar el repositorio:**  
2. **Instalar dependencias:**  
```
> composer install
```

3. **Configurar el archivo .env:**  
busque la line DATABASE_URL y modifique el usuario y la contraseña de la base de datos.  
4. **Generar la clave secreta:**  
   
```
> php bin/console secrets:generate-keys  
```
5. **Configurar la base de datos:**    
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
6. **Iniciar el servidor local:**  
```
 symfony server:start
```
##Api Rest  
1. login    
url: /api/login_check   
metodo: POST   
parametros:  
```
{
    "email":"tu correo ",
    "password":"tu contraseña"
}
```
2. Listar productos  
url: /api/list/product  
metodo: GET  
Autenticación: Bearer Token  
  
3. Crear productos  
url: /api/create/products  
metodo: POST  
Autenticación: Bearer Token  
Nota: puedes crear tantos productos como desees.  
parametros:
```
   {
    "products":[
        {
            "sku": "2002eve1",
            "product_name": "producto prueba1"
        },
        {
            "sku": "2002eve2",
            "product_name": "producto prueba2",
            "description": "producto 2 prueba"
        }
    ]
}

```
4. Actualizar productos  
url: /api/update/products  
metodo: POST  
Autenticación: Bearer Token  
Nota: puedes actualizar tantos productos como desees.  
parametros:   
```
{
     "products":[{
             "id":"3007",
             "sku":"2002eve8",
             "product_name":"",
             "description":"hola estamos actualizando"
           }]
}
```
