<?php
echo '<!DOCTYPE html>';
echo "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml">';
echo "\n";
include 'application/views/html_head.php';
echo "\n<body>\n";
include 'application/views/header.php';
include 'application/views/admin_menu.php';

echo "\n<div id='wrapper'>\n";
include 'application/views/sidebar.php';
echo "\n<div id='content'>\n";

$this->renderAdminArticles();

echo "\n<div id='pagination'>\n";
echo '<ul>';

echo $paginationHtmlPart;
include 'selectNewsPerPage.php';

echo '</ul>';
echo '</div>';


echo '</div>';
echo '<div class="clear"></div>';

echo '</div>';
include 'application/views/footer.php';
echo "\n</body>\n";
echo "\n</html>\n";