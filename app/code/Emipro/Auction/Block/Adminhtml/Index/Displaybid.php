<?php
namespace Emipro\Auction\Block\Adminhtml\Index;

use \Magento\Framework\App\ResourceConnection;

class Displaybid extends \Magento\Backend\Block\Template
{
    protected $_template = 'auction_bid.phtml';
    protected $blockGrid;
    protected $registry;

    protected $jsonEncoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Emipro\Auction\Model\BidFactory $bidFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->_bidFactory = $bidFactory;
        $this->_resource = $resource;
        parent::__construct($context, $data);
    }

    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Emipro\Auction\Block\Adminhtml\Index\Edit\Tab\Bid',
                'auction.bid.grid'
            );
        }
        return $this->blockGrid;
    }
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }
    public function getAttachmentJson()
    {
        $id = $this->getRequest()->getParam('auction_id');
        $customer = $this->_resource->getTableName('customer_grid_flat');
        $collection = $this->_bidFactory->create()->getCollection()->addFieldToFilter('auction_id', $id);
        $collection->getSelect()->join(['bidder' => $customer], 'bidder.entity_id = main_table.customer_id', ['bidder_name' => 'bidder.name']);
        if (!empty($collection)) {
            return $this->jsonEncoder->encode($collection);
        }
        return '{}';
    }
}
