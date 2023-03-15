<p align="center">
  <img src="https://www.factoriadigital.com/blog/wp-content/uploads/2014/10/logo-de-magento.png" alt="Magento" width="240px">
</p>

# cobru magento
Plugin de pago cobru para prestashop
**Si usted tiene alguna pregunta o problema, no dude en comentarlo en este repositorio.**

## Descargar
* [cobru plugin para magento v1.0.0](https://github.com/oscar-rey-mosquera/cobru_plugin_magento/releases/download/1.0.1/plugin_cobru_magento_v1.0.0.zip).

## Requisitos

* Tener una cuenta activa en [cobru](https://cobru.co/).
* Tener instalado magento v2.
* Acceso a la carpeta app de tu instalación de magento v2.
* Acceso al admin de magento.

## Instalación
1- Descargar el plugin y descomprime.

* [cobru plugin para magento v1.0.0](https://github.com/oscar-rey-mosquera/cobru_plugin_magento/releases/download/1.0.1/plugin_cobru_magento_v1.0.0.zip).


2- Descomprime el archivo zip descargado, copia la carpeta Cobru y llevarla a su instalación de magento 2 en la ruta /app/code/

3- Dirigirse a la ruta de instalación de su magento 2 y ejecutar los siguientes comandos
```
php bin/magento module:enable Cobru_Main
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```
4- Si desea puede ejecutar el siguiente comando para verificar que el modulo esté habilitado
```
php bin/magento module:status
```

## Finalización

Ya puede ingresar al área de administración de Magento 2 e ingresar a Tiendas->configuracion->Metodos de pago
y encontrará el panel de cobru para configurarlo.

<img src="/imagenes/1.jpg" width="400px"/>


