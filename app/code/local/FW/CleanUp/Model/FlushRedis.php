<?php
    chdir(dirname(__FILE__));  // Change working directory to script location
    require_once '../../../../../Mage.php';  // Include Mage
    Mage::app('admin');  // Run Mage app() and set scope to admin

    $redisClean = new FW_CleanUp_Model_Clean();
    $redisClean->flushRedis();

?>