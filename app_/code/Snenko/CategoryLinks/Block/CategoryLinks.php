<?php

namespace Snenko\CategoryLinks\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class CategoryLinks extends Template
{
    protected $categoryCollectionFactory;
    protected $storeManager;

    public function __construct(
        Template\Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getCategoryById($categoryId)
    {
        return $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($categoryId)
            ->getFirstItem();
    }


    public function getCategoryCollection()
    {
        $store = $this->storeManager->getStore();
        $rootCategoryId = $store->getRootCategoryId();

        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addIsActiveFilter()
            ->addLevelFilter(2) // Получение только категорий второго уровня
            ->addRootLevelFilter($rootCategoryId);

        return $collection;
    }
}
