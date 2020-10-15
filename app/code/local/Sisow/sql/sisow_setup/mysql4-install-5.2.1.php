
<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales/order')}

ADD COLUMN `sisow_fee` decimal(12,4) default NULL,

ADD COLUMN `sisow_fee_tax` decimal(12,4) default NULL,

ADD COLUMN `base_sisow_fee` decimal(12,4) default NULL,

ADD COLUMN `base_sisow_fee_tax` decimal(12,4) default NULL,

ADD COLUMN `sisow_fee_incl_tax` decimal(12,4) default NULL,

ADD COLUMN `base_sisow_fee_incl_tax` decimal(12,4) default NULL;

");