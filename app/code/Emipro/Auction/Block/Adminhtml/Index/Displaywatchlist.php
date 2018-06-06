<?php
namespace Emipro\Auction\Block\Adminhtml\Index;

use \Magento\Framework\App\ResourceConnection;

class Displaywatchlist extends \Magento\Backend\Block\Template
{
    protected $_template = 'auction_bid.phtml';
    protected $blockGrid;
    protected $registry;
    protected $jsonEncoder;
    protected $_auctionFactory;
    protected $_customerFactory;
    protected $_resource;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Emipro\Auction\Model\CustomerFactory $customerFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->_auctionFactory = $auctionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_resource = $resource;
        parent::__construct($context, $data);
    }

    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Emipro\Auction\Block\Adminhtml\Index\Edit\Tab\Watchlist',
                'auction.watchlist.grid'
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
        $auctionModel = $this->_auctionFactory->create()->load($id);
        $pro_id = $auctionModel->getProductId();
        $collection = $this->_customerFactory->create()->getCollection()->addFieldToFilter('watch_list', $pro_id);
        $collection->getSelect()->join(['bidder' => $customer], 'bidder.entity_id = main_table.customer_id', ['bidder_name' => 'bidder.name', 'email' => 'bidder.email']);
        if (!empty($collection)) {
            return $this->jsonEncoder->encode($collection);
        }
        return '{}';
    }
}
