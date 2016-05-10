<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE {$this->getTable('novaposhta_city')} (
  `id` int(10) unsigned NOT NULL,
  `ref` varchar(100),
  `name_ru` varchar(100),
  `name_ua` varchar(100),
  `updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`ref`),
  INDEX `name_ru` (`name_ru`),
  INDEX `name_ua` (`name_ua`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('novaposhta_warehouse')} (
  `id` int(10) unsigned NOT NULL,
  `city_id` varchar(100),
  `ref` varchar(100),
  `address_ru` varchar(200),
  `address_ua` varchar(200),
  `phone` varchar(100),
  `longitude` float(10,6),
  `latitude` float(10,6),
  `number_in_city` int(3) unsigned NOT NULL,
  `updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (`city_id`) REFERENCES `{$this->getTable('novaposhta_city')}` (`ref`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();