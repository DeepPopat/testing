<?php
namespace Emipro\Smartproductselector\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager as ObjManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class Applyrules extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * Apply all active catalog price rules
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private $objectMan;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        Date $dateFilter
    ) {

        parent::__construct($context, $coreRegistry, $dateFilter);
    }
    public function execute()
    {
        try {
            $rule_id = $this->getRequest()->getParam('rule_id');
            $this->objectMan = ObjManager::getInstance();
            $this->applyrule($rule_id);
            $this->objectMan->create('Emipro\Smartproductselector\Model\Flag')->loadSelf()->setState(0)->save();

            $this->messageManager->addSuccess(__('The rules have been applied.'));
        } catch (\Exception $e) {
            $this->messageManager->addError('Unable to apply rules.');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('smartproductselector/*/');
    }

    protected function applyrule($id)
    {
        $resource = $this->objectMan->create('Magento\Framework\App\ResourceConnection');
        $writeConnection = $resource->getConnection('default');
        $table = $resource->getTableName('emipro_smartproductselector_products');

        $data = $this->objectMan->create('Emipro\Smartproductselector\Model\Rule')->load($id);
        $pids = $data->getMatchingProductIds();

        $cod_id = $id;
        $fields1['rule_id'] = $cod_id;
        $condition = [$writeConnection->quoteInto('rule_id = ?', $cod_id)];
        $writeConnection->DELETE($table, $condition);
        if ($data['is_active'] == 1) {
            $this->insertIds($cod_id, $pids);
        }
    }

    protected function insertIds($cod_id, $pids)
    {
        $scopeconfig = $this->objectMan->create("Magento\Framework\App\Config\ScopeConfigInterface");
        $resource = $this->objectMan->create('Magento\Framework\App\ResourceConnection');
        $logger = $this->objectMan->get('\Psr\Log\LoggerInterface');
        $helper = $this->objectMan->create('Emipro\Smartproductselector\Helper\Data');
        $writeConnection = $resource->getConnection('default');
        $table = $resource->getTableName('emipro_smartproductselector_products');
        $fields = [];
        try {
            foreach ($pids as $key => $value) {
                if ($value[1] == 1 && $value[0] == 1) {
                    $fields[] = [
                        'entity_id' => $key,
                        'rule_id' => $cod_id,
                    ];
                }
            }
            if (!empty($fields)) {
                $writeConnection->insertMultiple($table, $fields);
                $scopeconfigVal = $scopeconfig->getValue("smartproductselector/smarproductconfig/productCountAdmin", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($scopeconfigVal == 1) {
                    $helper->getSmartApplyData($cod_id);
                }
            }
        } catch (LocalizedException $e) {
            $logger->addDebug(print_r($e->getMessage(), true));
        }
    }
}
