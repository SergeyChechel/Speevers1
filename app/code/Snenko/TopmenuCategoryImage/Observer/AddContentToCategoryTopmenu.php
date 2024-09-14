<?php

namespace Snenko\TopmenuCategoryImage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Class AddFirstCategoryImageToTopmenu
 * @package VendorModuleName
 */
class AddContentToCategoryTopmenu implements ObserverInterface
{
    /**
     * @var CategoryRepositoryInterface $categoryRepository
     */
    protected $categoryRepository;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;
    protected $storeManager;


    /**
     * AddFirstCategoryImageToTopmenu constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository repository
     */

    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        AssetRepository $assetRepository
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @param Observer $observer Observer object
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getTransport();
        $html      = $transport->getHtml();
        $menuTree  = $transport->getMenuTree();
        $children  = $menuTree->getChildren();

        foreach ($children as $child) {
            if ($child->getLevel() != 1) continue;
            $menuId = $child->getId();
            $extraHtml = '<img id="'.$menuId.'" src="'.$this->getCategoryImage($menuId)[0].'" class="mob-cat-img"/><div class="descr-wrap">';
            $extraHtml1 = '<span id="'.$menuId.'-descr'.'" class="mob-cat-descr">'. $this->getCategoryImage($menuId)[1] .'</span></div>';

            $pattern = '/(<span>' . preg_quote($child->getName(), '/') . '<\/span>)/';

            $html = preg_replace(
                $pattern,
                $extraHtml . '$1' . $extraHtml1 . '$2',
                $html
            );
        }

        $transport->setHtml($html);
    }

    /**
     * Retrieves the category image for the corresponding child
     *
     * @param string $categoryId Category composed ID
     *
     * @return string
     */
    protected function getCategoryImage($categoryId)
    {
        $categoryIdElements = explode('-', $categoryId);
        $category = $this->categoryRepository->get(end($categoryIdElements));

        $image_url = $category->getImageUrl() ?? $this->getDefaultCategoryImage();
        $cat_descr = $category->getDescription();
        $plainTextDescription = '';
        if ($cat_descr) {
            preg_match('/<p>(.*?)<\/p>/', $cat_descr, $matches);
            $plainTextDescription = isset($matches[1]) ? $matches[1] : '';
        }

        return [$image_url, $plainTextDescription];
    }

    protected function getDefaultCategoryImage()
    {
        // Генерируем URL для дефолтного изображения
        return $this->assetRepository->getUrl('images/default/default_category_image.jpg');
    }


    /**
     * Check if current menu element corresponds to a category
     *
     * @param string $menuId Menu element composed ID
     *
     * @return string
     */
    protected function isCategory($menuId)
    {
        $menuId = explode('-', $menuId);

        return 'category' == array_shift($menuId);
    }
}
