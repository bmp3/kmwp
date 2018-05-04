<?php
/**
* Чистый Шаблон для разработки
* Это шаблон 404 ошибки, отрабатывает, когда написали фигни в адресную строку
* @package WordPress
* @subpackage clean
*/

get_header(); // Подключаем хедер ?> 
<h1 style="text-align:center;">404, no such page</h1>
<?php get_sidebar();  // Подключаем сайдбар ?>
<?php get_footer(); // Подключаем футер ?>