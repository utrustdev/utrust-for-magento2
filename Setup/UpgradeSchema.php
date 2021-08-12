<?php                                                                 
namespace Utrust\Payment\Setup;                                              
use Magento\Framework\Setup\ModuleContextInterface;                         
use Magento\Framework\Setup\SchemaSetupInterface;                           
use Magento\Framework\Setup\UpgradeSchemaInterface;                       
class UpgradeSchema implements UpgradeSchemaInterface{
protected $installer;
/**
 * {@inheritdoc}
 */
public function upgrade(
    SchemaSetupInterface $setup,
    ModuleContextInterface $context
) {
    $this->installer = $setup;
    $this->installer->startSetup();
    if (version_compare($context->getVersion(), "1.2.1", "<")) {
        //Add columns to existing quote table
        $this->addQuoteColumn($setup);
    }
    $this->installer->endSetup();
}

protected function addQuoteColumn($setup)
{
    $installer = $setup;
    $table_columns = [
        'utrust_payment_id' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment'  => 'utrust_payment_id'
        ]
    ];
    $connection = $installer->getConnection();

    foreach ($table_columns as $name => $definition) {
        $connection->addColumn($installer->getTable('quote_payment'), $name, $definition);
        $connection->addIndex(
            $installer->getTable('quote_payment', 'utrust_payment_id'),
            $installer->getIdxName('quote_payment', ['utrust_payment_id'], '', 'utrust_payment_id'),
            ['utrust_payment_id']
        );
    }
}}