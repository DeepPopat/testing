<?php
namespace Emipro\Smartproductselector\Block\Adminhtml\Grid;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;

    protected $_gridFactory;

    protected $_status;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Emipro\Smartproductselector\Model\RuleFactory $gridFactory,
        \Emipro\Smartproductselector\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('smartproductselectorGrid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('grid_record');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_gridFactory->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'rule_id',
            [
                'header' => __('ID'),
                'width' => '100px',
                'type' => 'number',
                'index' => 'rule_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Rule Name'),
                'index' => 'rule_name',
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'alternative_type',
            [
                'header' => __('Alternative Type'),
                'index' => 'alternative_type',
                'type' => 'options',
                'options' => $this->_status->toAlternativeOptionArray(),
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => $this->_status->toOptionArray(),
            ]
        );
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('smartproductselector/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['rule_id' => $row->getId()]
        );
    }
}
