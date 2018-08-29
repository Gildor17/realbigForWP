=== Realbig For WordPress ===
Tags: AD, content filling
Requires at least: 4.0
Tested up to: 4.9.6
Stable tag: 0.1.4a
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Realbig's plugin for WordPress, which purpose is adding AD in your blogs, articles and other posts.

== Description ==

plugin purpose is filling content of blogs and themes with AD blocks

== Installation ==

Здесь описаны основные правила по установке и настройке плагина.

Со стороны вашего сайта на Wordpress:
1. Скачайте плагин и распакуйте его в `~your wordpress site~/wp-content/plugins/` .
    Примечание: Если вы скачивали с Github, то из названия папки уберите "-master";
2. Активируйте плагин на вашем сайте;
3. После активации, зайдите на вкладку "реалбиг плагин настройки", где в поле "Токен" введите токен, взятый с реалбига и синхронизируйтесь.

Со стороны Realbig:
Примечания:
 - ваш блок должен быть отмечен, как "активный" на странице "ВП плагин", что бы при синхронизации настройки этого плагина учитывались.

English:
This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `~your wordpress site~/wp-content/plugins/` directory;
2. Activate the plugin through the 'Plugins' screen in WordPress;
3. Go to your page on Realbig and configure blocks for your purposes;
4. Open settings page of plugin on your wordpress site;
5. Copy "token" from blocks settings page on Realbig and paste it in the field on page, which you opened on previous step.

== Changelog ==

= 0.11a =
* Fixed problem with token synchronizing and saving;
* Added possibility to remove blocks without chosen names from plugin settings page on realbig's side.

= 0.1a =
* First release of plugin, alpha version.

== Upgrade Notice ==

= 0.11a =
Some minor updates to make settings process more comfortable.