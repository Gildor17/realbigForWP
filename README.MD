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

Установка и настройка плагина.

Со стороны Realbig: 

- Зарегистрировать сайт на реалбиг;
- Создать и настроить хотя бы 1н блок для этого сайта;
- Перейти во вкладку "ВП плагин" либо с помощью кнопки "WP" во вкладке сайты;
- Выбрать сайт (если переходили по вкладке "ВП плагин");
- Настроить позиционирование блоков на страницах выбраного вами сайта и сохранить настройки;
- Токен появится ниже после выбора сайта;

Со стороны вашего сайта на Wordpress:

- Скачайте плагин;
- Активируйте плагин на вашем сайте;
- После активации, зайдите на вкладку "реалбиг плагин настройки", где в поле "Токен" введите токен, взятый с реалбига и синхронизируйтесь.

Примечания:

значения настроек блока:
1) "блок" - название блока, настроеного для сайта во вкладке "сайты";
2) "тип отображения" - выбор принцыпа позиционирования блока на вашем сайте:
	2.1) "одиночный блок" - блок позиционируется относительно Html-тега;
	2.2) "конкретный элемент" - блок позиционируется по ид/классу элемента;
	2.3) "в конце контента" - блок ставится в конце текста, в основном, до комментариев (зависит темы или других настроек вордпреса).
3) "тег" - Html-тег елемента, возле которого ставится реклама, "р" - абзац, "н1-н6" - заголовки и тд.;
4) "позиция тега" - позиционирование блока до или после выбранного тега;
5) "место тега" - выбор номера тега по счету, относительно которого будет вставлятся блок, при положительном значении считает теги с начала контента до конца, а при отрицательном значении считает блоки с конца контента;
6) "конкретный элемент" - название ид/класса элемента (писать в форматах "#название_ид/.название_класса"), возле которого ставится блок;
7) "минимум символов" - минимальное количество символов в контенте, которое нужно, что бы блок отображался (0 или пусто - без ограничений);
8) "минимум заголовков" - минимальное количество заголовков (h1-h6) в контенте, которое нужно, что бы блок отображался (0 или пусто - без ограничений), основной заголовок тоже учитывается;
9) "активный" (checkbox) - активация или деактивация блока. Неактивные блоки не учитываются при синхронизации;
10) "удалить" - удаление настроек для блока;

- "Добавить скрипт для PUSH в head сайта" (checkbox) - при включении, после сохранения и синхронизации, плагин будет вставлять в Head сайта код, который нужен для отображения блоков;

- На RealBig, на странице "ВП плагин", ваш блок должен быть отмечен, как "активный", что бы при синхронизации настройки для этого блока учитывались.

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