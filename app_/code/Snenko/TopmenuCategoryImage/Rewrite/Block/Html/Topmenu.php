<?php

namespace Snenko\TopmenuCategoryImage\Rewrite\Block\Html;

use Magento\Theme\Block\Html\Topmenu as MagentoTopmenu;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DataObject;

class Topmenu extends MagentoTopmenu
{
    /**
     * Recursively generates top menu HTML from data that is specified in $menuTree.
     *
     * @param Node   $menuTree          Menu tree node
     * @param string $childrenWrapClass Class for wrapping child nodes
     * @param int    $limit             Limit
     * @param array  $colBrakes         Column breaks
     * @return string
     *
     * @SuppressWarnings(PHPMD)
     */
    protected function _getHtml(
        Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        // Генерация HTML с использованием родительского метода
        $html = parent::_getHtml($menuTree, $childrenWrapClass, $limit, $colBrakes = []);

        if ($menuTree->getLevel() == 0) {
            $transportObject = new DataObject(['html' => $html, 'menu_tree' => $menuTree]);
            $this->_eventManager->dispatch(
                'vendor_topmenu_node_gethtml_after',
                ['menu' => $this->_menu, 'transport' => $transportObject]
            );
            $html = $transportObject->getHtml();
        }

        return $html;
    }
}
