<?php

namespace ArtCategorySearch\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Db;
use Tools;
use Exception;
use Context;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminCategorySearchController extends FrameworkBundleAdminController
{
    public function displayAjaxSearchCategories()
    {
        $query = Tools::getValue('query');
        $idLang = Context::getContext()->language->id;
    
        if (!$query) {
            die(json_encode(['error' => 'No query provided']));
        }
    
        try {
            $results = Db::getInstance()->executeS('
                SELECT id_category, name 
                FROM `' . _DB_PREFIX_ . 'category_lang`
                WHERE name LIKE "%' . pSQL($query) . '%" 
                AND id_lang = ' . (int) $idLang . ' 
                AND id_category != 9
            ');

            die(json_encode($results));
        } catch (Exception $e) {
            die(json_encode(['error' => $e->getMessage()]));
        }
    }

    public function displayAjaxAddCategoryAssociation()
    {
        $idArtCategory = 9;
        $idAssociatedCategory = (int) Tools::getValue('id_associated_category');

        if (!$idAssociatedCategory) {
            return new JsonResponse(['error' => 'ID de la catégorie associée manquant'], 400);
        }

        try {
            Db::getInstance()->insert('art_category_association', [
                'id_art_category' => $idArtCategory,
                'id_associated_category' => $idAssociatedCategory,
            ]);

            return new JsonResponse(['success' => true]);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function displayAjaxGetAssociations()
{
    $idArtCategory = 9;
    $idLang = Context::getContext()->language->id;

    $results = Db::getInstance()->executeS('
        SELECT ac.id_associated_category, cl.name 
        FROM ' . _DB_PREFIX_ . 'art_category_association ac
        JOIN ' . _DB_PREFIX_ . 'category_lang cl ON ac.id_associated_category = cl.id_category
        WHERE ac.id_art_category = ' . (int) $idArtCategory . '
        AND cl.id_lang = ' . (int) $idLang
    );

    return new JsonResponse($results);
}

public function displayAjaxDeleteAssociation()
{
    $idArtCategory = 9;
    $idAssociatedCategory = (int) Tools::getValue('id_associated_category');

    if (!$idAssociatedCategory) {
        return new JsonResponse(['error' => 'ID de la catégorie associée manquant'], 400);
    }

    try {
        Db::getInstance()->delete(
            'art_category_association',
            'id_art_category = ' . (int) $idArtCategory . ' AND id_associated_category = ' . (int) $idAssociatedCategory
        );

        return new JsonResponse(['success' => true]);
    } catch (Exception $e) {
        return new JsonResponse(['error' => $e->getMessage()], 500);
    }
}

}
