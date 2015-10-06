<?php
/**
 * this process sets up the unspsc and punchout export fields for the cateogry.
 *
 * if the fields already exist, then nothing is changed.
 *
 */

/** @var $installer Mage_Core_Model_Resource_Setup  */

$installer = $this;

$setup = new Vbw_Punchout_Model_Resource_Setup();
$setup->run();
