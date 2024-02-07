<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Utils\Help\Category;
use Duplicator\Utils\Help\Help;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

/** @var Category[] $categories */
$categories = $tplData['categories'];
?>
<ul class="duplicator-help-category-list">
<?php foreach ($categories as $category) : ?>
    <li class="duplicator-help-category">
        <header>
            <i class="fa fa-folder-open"></i>
            <span><?php echo esc_html($category->getName()); ?></span>
            <i class="fa fa-angle-right"></i>
        </header>
        <?php if (count($category->getChildren()) > 0) { ?>
            <?php $tplMng->render('parts/help/category-list', ['categories' => $category->getChildren()]);
        } ?>
        <?php if ($category->getArticleCount() > 0) : ?>
            <?php $tplMng->render(
                'parts/help/article-list',
                [
                    'articles'   => Help::getInstance()->getArticlesByCategory($category->getId()),
                    'list_class' => 'duplicator-help-article-list',
                ]
            ); ?>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>
