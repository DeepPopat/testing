<?php
namespace Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab;

class Productgrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_productFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectMan,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->objectMan = $objectMan;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['in_products' => 1]);
        $this->setSaveParametersInSession(false);
    }

    protected function _prepareCollection()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $store = $this->_storeManager->getStore($storeId);
        $collection = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addFieldToFilter("sku", ["neq" => "customoptionmaster"]);
        if ($this->_moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        }

        $collection->addAttributeToSelect('price');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'type' => 'checkbox',
                'html_name' => 'products_id',
                'required' => true,
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'sku',
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->objectMan->get('Magento\Catalog\Model\Product\Type')->getOptionArray(),
            ]
        );

        $sets = $this->objectMan->create('Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection')
            ->setEntityTypeFilter($this->objectMan->create('Magento\Catalog\Model\Product')
                    ->getResource()
                    ->getTypeId())
            ->load()
            ->toOptionHash();
        $this->addColumn(
            'set_name',
            [
                'header' => __('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'width' => '80px',
                'index' => 'sku',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'width' => '80px',
                'index' => 'name',
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _getSelectedProducts()
    {
        $products = array_keys($this->getSelectedProducts());
        return $products;
    }

    public function getSelectedProducts()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $prodId = [];
        $result = [];
        if (isset($ruleId) && !empty($ruleId)) {
            $smartProductData = $this->objectMan->create("Emipro\Smartproductselector\Model\Rule")->load($ruleId);
            $result = explode(',', $smartProductData['sku_data']);
        }
        foreach ($result as $obj) {
            $prodId[$obj] = ['position' => "0"];
        }

        return $prodId;
    }
}
