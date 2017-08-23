<?php
class FW_CleanUp_Model_Clean {
    
	public function flushRedis()
	{
		//Initialize Redis connection variables
		$objIP = Mage::getConfig()->getNode('global/cache/backend_options')->server;
 		$objPort = Mage::getConfig()->getNode('global/cache/backend_options')->port;

		$fpcIP = Mage::getConfig()->getNode('global/full_page_cache/backend_options')->server;
		$fpcPort = Mage::getConfig()->getNode('global/full_page_cache/backend_options')->port;

		//log settting values
		Mage::log('OBJ IP:'.$objIP, null, 'redis_flush_cron.log');
		Mage::log('OBJ PORT:'.$objPort, null, 'redis_flush_cron.log');
		Mage::log('FPC IP:'.$fpcIP, null, 'redis_flush_cron.log');
		Mage::log('FPC PORT:'.$fpcPort, null, 'redis_flush_cron.log');
	
		//Connect to object cache
		$objRedis = new Redis();
		$objRedis->connect((string)$objIP, (string)$objPort);

		//Connect to object cache
 		$objRedis = new Redis();
 		$objRedis->connect((string)$objIP, (string)$objPort);
	
		//Connect to full page cache
 		$fpcRedis = new Redis();
 		$fpcRedis->connect((string)$fpcIP, (string)$fpcPort);

		try{
			//Run INFO Command for both caches
			$keyspaceObj =  $objRedis->info("KEYSPACE");
			$keyspaceFpc =  $fpcRedis->info("KEYSPACE");
    		} catch(Exception $e) {
			Mage::log('Redis Error:'.$e->getMessage(), null, 'redis_flush_cron.log');
			Mage::log('Stack Trace:'.$e->getTraceAsString(), null, 'redis_flush_cron.log');
			$to = "eCommDevTeam@fwmedia.com";
			$subject = "Nightly Redis Flush Failed";
			mail($to, $subject, '');
			return;
                }

		//Log KeySpace db0 data before flush
		Mage::log('Object Keyspace before flush:'.$keyspaceObj['db0'], null, 'redis_flush_cron.log');
		Mage::log('FPC Keyspace before flush:'.$keyspaceFpc['db0'], null, 'redis_flush_cron.log');


		try{
			//Flush Object Cache
	 		Mage::dispatchEvent('adminhtml_cache_flush_all');
			Mage::app()->getCacheInstance()->flush();

			// Flush Full Page Cache
			Enterprise_PageCache_Model_Cache::getCacheInstance()->clean(Enterprise_PageCache_Model_Processor::CACHE_TAG);       

			//Run INFO Command for both caches
			$keyspaceObj =  $objRedis->info("KEYSPACE");
			$keyspaceFpc =  $fpcRedis->info("KEYSPACE");
		} catch(Exception $e) {
			Mage::log('Redis Error:'.$e->getMessage(), null, 'redis_flush_cron.log');
			Mage::log('Stack Trace:'.$e->getTraceAsString(), null, 'redis_flush_cron.log');
			$to = "eCommDevTeam@fwmedia.com";
			$subject = "Nightly Redis Flush Failed";
			mail($to, $subject, '');
			return;
                }
	
		//Log KeySpace db0 data after flush
		Mage::log('Object Keyspace after flush:'.$keyspaceObj['db0'], null, 'redis_flush_cron.log');
		Mage::log('FPC Keyspace after flush:'.$keyspaceFpc['db0'], null, 'redis_flush_cron.log');
 
		//clean dataflow_batch_export table
		$this->cleanDataFlowBatchExport();
 	
		//clean report_viewed_product_index table
		$this->cleanReportViewedProductIndex();
    }
    
        public function cleanDataFlowBatchExport($rowLimit = 50000) 
    {
        $connection = Mage::getSingleton('core/resource')->getConnection();
        
        //Check if dataflow_batch_export has exceeds $rowLimit records, and if so, truncate it        
        $dataflowCount = $connection->fetchOne( 'SELECT COUNT(*) AS count FROM dataflow_batch_export' );
        if($dataflowCount > $rowLimit) {
            $connection->truncateTable('dataflow_batch_export');
        }        
    }
    
    public function cleanReportViewedProductIndex($rowLimit = 500000) 
    {        
        $connection = Mage::getSingleton('core/resource')->getConnection();
        
        //Check if report_viewed_product_index table exceeds $rowLimit records. If so, truncate it
        $reportCount = $connection->fetchOne( 'SELECT COUNT(*) AS count FROM report_viewed_product_index' );
        if($reportCount > $rowLimit) {
            $connection->truncateTable('report_viewed_product_index');
        }
        
    }
}
