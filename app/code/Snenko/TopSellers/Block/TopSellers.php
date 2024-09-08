<?php
namespace Snenko\TopSellers\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestsellersCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Helper\Image as ImageHelper;

class TopSellers extends Template
{
    protected $productCollectionFactory;
    protected $bestsellersCollectionFactory;
    protected $resourceConnection;
    protected $imageHelper;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        BestsellersCollectionFactory $bestsellersCollectionFactory,
        ResourceConnection $resourceConnection,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->bestsellersCollectionFactory = $bestsellersCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    public function getTopSellingProducts($limit = 4)
    {
        $bestsellers = $this->bestsellersCollectionFactory->create()
            ->setPageSize($limit)
            ->setOrder('qty_ordered', 'DESC');

        $productIds = [];
        foreach ($bestsellers as $item) {
            $productIds[] = $item->getProductId();
        }

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $productIds]);

        return $collection;
    }


    public function getTopSellingProductsByOrderItem($limit = 4)
    {
        // Получаем соединение с базой данных
        $connection = $this->resourceConnection->getConnection();

        // Создаем SQL-запрос для выборки товаров с наибольшим количеством продаж
        $select = $connection->select()
            ->from(
                ['order_item' => $connection->getTableName('sales_order_item')],
                ['product_id', 'qty_ordered' => 'SUM(order_item.qty_ordered)'] // Суммируем количество проданных товаров
            )
            ->group('product_id') // Группируем по product_id
            ->order('qty_ordered DESC') // Сортируем по количеству заказанных товаров
            ->limit($limit); // Ограничиваем количество товаров

        // Получаем массив данных: product_id и qty_ordered
        $topSellingProducts = $connection->fetchAll($select);

        // Массив для хранения product_ids и данных о продажах
        $productIds = [];
        $salesData = [];
        foreach ($topSellingProducts as $item) {
            $productIds[] = $item['product_id'];
            $salesData[$item['product_id']] = $item['qty_ordered']; // Записываем количество продаж для каждого товара
        }

        // Формируем коллекцию товаров по найденным идентификаторам
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*') // Добавляем все нужные атрибуты товаров
            ->addFieldToFilter('entity_id', ['in' => $productIds]); // Фильтруем по найденным product_id

        // Привязываем данные о количестве продаж к товарам коллекции
        foreach ($collection as $product) {
            $product->setData('qty_ordered', $salesData[$product->getId()]); // Устанавливаем количество продаж
        }

        // Преобразуем коллекцию в массив для сортировки
        $items = $collection->getItems();

        // Сортируем массив товаров по количеству продаж
        usort($items, function ($a, $b) {
            return $b->getData('qty_ordered') - $a->getData('qty_ordered');
        });

        // Возвращаем отсортированный массив
        return $items;
    }


    /**
     * Получает URL основного изображения товара.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductImageUrl($product)
    {
        // Инициализируем хелпер изображений и получаем URL
        return $this->imageHelper->init($product, 'product_base_image')->getUrl();
    }

}
