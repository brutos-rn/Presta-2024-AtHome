<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ArtCategorySearch extends Module
{
    public function __construct()
    {
        $this->name = 'artcategorysearch';
        $this->version = '1.1.0';
        $this->author = 'Your Name';
        $this->tab = 'administration';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Art Category Association Search');
        $this->description = $this->l('Adds a search field in the Art category to find associated categories.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('ActionCategoryFormBuilderModifier')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayCategoryFooter')
            && $this->installDB();

    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDB();
    }

    private function installDB()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'art_category_association` (
            `id_art_category` int(11) UNSIGNED NOT NULL,
            `id_associated_category` int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_art_category`, `id_associated_category`),
            FOREIGN KEY (`id_art_category`) REFERENCES `' . _DB_PREFIX_ . 'category`(`id_category`) ON DELETE CASCADE,
            FOREIGN KEY (`id_associated_category`) REFERENCES `' . _DB_PREFIX_ . 'category`(`id_category`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    private function uninstallDB()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'art_category_association`';
        return Db::getInstance()->execute($sql);
    }

    public function hookActionCategoryFormBuilderModifier($params)
    {
        $categoryId = (int) $params['id'];

        if ($categoryId === 9) {
            $formBuilder = $params['form_builder'];

            $formBuilder->add('category_search', TextType::class, [
                'label' => $this->l('Rechercher des catégories associées'),
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => $this->l('Tapez pour rechercher...'),
                    'class' => 'category-search-field',
                    'data-category-id' => $categoryId
                ]
            ]);
        }
    }

    protected function registerRoute()
    {
        $router = $this->get('prestashop.module.router');
        

        $router->add(
            'artcategorysearch_search',
            [
                'controller' => 'ArtCategorySearch\Controller\AdminCategorySearchController',
                'action' => 'displayAjaxSearchCategories',
                'methods' => ['GET'],
                'path' => '/artcategorysearch/search',
            ]
        );
    

        $router->add(
            'artcategorysearch_add_association',
            [
                'controller' => 'ArtCategorySearch\Controller\AdminCategorySearchController',
                'action' => 'displayAjaxAddCategoryAssociation',
                'methods' => ['POST'],
                'path' => '/artcategorysearch/add-association',
            ]
        );
    
        $router->add(
            'artcategorysearch_delete_association',
            [
                'controller' => 'ArtCategorySearch\Controller\AdminCategorySearchController',
                'action' => 'displayAjaxDeleteCategoryAssociation',
                'methods' => ['POST'],
                'path' => '/artcategorysearch/delete-association',
            ]
        );
    }
    

    public function hookActionDispatcher($params) {
        $this->registerRoute();
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') === 'AdminCategories') {
            $this->context->controller->addCSS($this->_path . 'views/css/style.css');
            $this->context->smarty->assign(array(
                'js_path' => $this->_path . 'views/js/main.js'
            ));
            return $this->display(__FILE__, 'views/templates/hook/displayBackOfficeHeader.tpl');
        }
    }

    public function hookDisplayCategoryFooter($params)
    {
        if ($this->context->controller->php_self === 'category' && (int)$this->context->controller->getCategory()->id === 9) {
            $idCategory = 9;
            $associatedCategories = $this->getAssociatedCategories($idCategory);
            $this->context->smarty->assign([
                'associatedCategories' => $associatedCategories,
                'link' => $this->context->link,
            ]);
    
            return $this->display(__FILE__, 'views/templates/hook/associated_categories.tpl');
        }
        
        return '';
    }

    public function getAssociatedCategories($idCategory)
    {
        $sql = 'SELECT c.id_category, cl.name,
                       CONCAT("' . _PS_BASE_URL_ . '/img/c/", c.id_category, ".jpg") AS image_url
                FROM ' . _DB_PREFIX_ . 'art_category_association AS aca
                JOIN ' . _DB_PREFIX_ . 'category AS c ON aca.id_associated_category = c.id_category
                JOIN ' . _DB_PREFIX_ . 'category_lang AS cl ON c.id_category = cl.id_category
                WHERE aca.id_art_category = ' . (int)$idCategory . ' 
                AND cl.id_lang = ' . (int)$this->context->language->id;
    
        return Db::getInstance()->executeS($sql);
    }

}
