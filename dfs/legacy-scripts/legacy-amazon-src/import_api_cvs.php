<?php
chdir(dirname(__FILE__));
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);

require '../includes/config.inc';
require '../models/amazon_api_m.inc';
require '../models/parser_m.inc';
require '../models/filter_m.inc';
require '../models/category_match_m.inc';
require '../models/category_m.inc';
require '../models/api_searches_m.inc';
require '../includes/common.inc';
require "../models/api/amazon.class.php";

set_time_limit(0);
ignore_user_abort(true);

echo date(DATE_RFC822) ."- Max exectutiong time set to: ".ini_get('max_execution_time')."\n";
echo date(DATE_RFC822) ."- Memory limit set to: ".ini_get('memory_limit')."\n";


$LOG_FILE = "$LOG_FOLDER/import_api_csv.$LOG_EXTENSION";

/* path to the lock file */
$LOCK_FILE_CSV = $DATAFILE_FOLDER . "_amazon/import_csv.lck";

print($LOCK_FILE_CSV);

$CATEGORY_MATCHES = array();
$SUB_CATEGORY_MATCHES = array();

function createLogFile($logFile) {
	@ unlink($logFile);
	@ touch($logFile);
	@ chmod($logFile, 0777);	
}

function shutdown() {
    global $LOCK_FILE_CSV;
    $error = error_get_last();
    
    if ($error !== NULL) {
        dslog("Fatal error occurred: " . print_r($error, true) . "\n");
    }
    
    // Clean up lock file
    if (file_exists($LOCK_FILE_CSV)) {
        unlink($LOCK_FILE_CSV);
    }
    
    // Close any open file handles
    if (isset($GLOBALS['fp']) && is_resource($GLOBALS['fp'])) {
        fclose($GLOBALS['fp']);
    }
    
    dslog("Shutdown: End of Script\n");
}

function dslog($message) {
	//$current = file_get_contents( $GLOBALS['LOG_FILE']);
	$current = "";
	// Append a new person to the file
	// Add memory usage logging
    $memUsage = memory_get_usage(true);
    $current .= "Memory Usage: " . ($memUsage / 1024 / 1024) . " MB\n";
	$current .= date(DATE_RFC822) . " : $message";
	file_put_contents($GLOBALS['LOG_FILE'], $current, FILE_APPEND | LOCK_EX);
	echo(date(DATE_RFC822) . " : $message");
	
}

function safe_die($message) {
	global $LOCK_FILE_CSV;
	
	if (file_exists($LOCK_FILE_CSV)) {
		unlink($LOCK_FILE_CSV);
	}
	
	die($message);
}

function resultset_to_array($resultset) {
	$items = array();
	
	while ($item = $resultset->fetch_array()) {
		array_push($items, $item);
	}
	
	return $items;
}

function copyProductsTableToTemporary($excluded_datafeed_ids) {
	global $db;
	dslog ("Copying products (except from IDs $excluded_datafeed_ids) into temporary table\n");
	$sql = "insert into temp_products select * from products where ( datafeed_id not in ($excluded_datafeed_ids) OR is_manual = 1 )  ";
	
	dslog("$sql\n");
	
	$result = $db->query($sql);

	if (! $result) {
		dslog("Error" . $db->error);
	}
}

function copyTemporaryToProductsTable() {
	global $db;
	dslog("Copying temporary products to main products table\n");
	dslog("...enabling keys on products table\n");
	$result = $db->query("alter table temp_products enable keys");
	if($result) {
		dslog("...dropping real products table\n");	
		$db->query("drop table products");
		dslog("...renaming temporary products table to real products table\n");	
		$db->query("rename table temp_products to products");
		dslog("...dropping temporary products table\n");	
		$db->query("drop table temp_products");
	}
}
	
function copyFeedProductsToTemporary() {
	global $db;
	$db->query ("insert into temp_products select * from products where ws_api_key is null OR is_manual = 1");	
}

function fetchFeed($datafeed) {
	global $DATAFILE_FOLDER;
	global $PHP_SHELL_COMMAND;
	global $SCRIPT_FOLDER;
	global $CATEGORY_MATCHES;	
	global $SUB_CATEGORY_MATCHES;	
		
	dslog("---------------------------------------------------------------\n");
	dslog(date('l jS \of F Y h:i:s A'));		
	dslog("Processing datafeed ID {$datafeed['id']} ({$datafeed['merchant_name']})\n");

	if ($datafeed['feed_type'] == 'automatic') {
		$datafeed['status'] = "Downloading";
		datafeed_update($datafeed);
	
		dslog ("Feed Type is Automatic. Downloading datafeed file\n");
		$datafile = $datafeed['id'] . ".dsf";

    $ch = curl_init($datafeed['url']);
    $fp = fopen("$DATAFILE_FOLDER/$datafile", "w");

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 300);

    curl_exec($ch);

		if(curl_errno($ch)) {
			dslog('WARNING! Curl error: #' . curl_errno($ch) . ' - '. curl_error($ch) . "\n");
		}
		
    curl_close($ch);
    fclose($fp);   

		chmod("$DATAFILE_FOLDER/$datafile", 0777);
		
		$parser = parser_find($datafeed['parser_id']);
		if ($parser['post_download_script']) {
			dslog ("Executing post download script {$parser['post_download_script']}\n");
			
			if (substr($parser['post_download_script'], 0, 1) != '/') {
				$scriptFolder = $SCRIPT_FOLDER . '/';
			} else {
				$scriptFolder = '';
			}
			
			system("$scriptFolder{$parser['post_download_script']} $DATAFILE_FOLDER/$datafile");
		} else {
			dslog("No post download script specified.\n");
		}
		
	} else {
		$datafile = $datafeed['datafile'];
		dslog ("Feed Type is Manual. Using file already uploaded ($datafile)\n");
	}
	
	return $datafile;
}

function parseApiFeedAI($datafeed, $datafile, $import_start_time) {
    global $db, $DATAFILE_FOLDER;
    
    // Set memory threshold (80% of memory_limit)
    $memoryLimit = intval(ini_get('memory_limit')) * 1024 * 1024 * 0.8;
    
    try {
        // Initialize file handles
        $fp = fopen($DATAFILE_FOLDER . '/' . $datafile, "r");
        if (!$fp) {
            throw new Exception("Could not open input file for reading");
        }
        
        $datafile_output = $datafeed['id'] . "-" . time() . ".dsf";
        $fp_output = fopen($DATAFILE_FOLDER."_amazon/temp/".$datafile_output, "w");
        if (!$fp_output) {
            fclose($fp);
            throw new Exception("Could not open output file for writing");
        }
        
        // Process file in chunks
        $buffer = '';
        $count = 0;
        $failed = 0;
        $chunk_size = 8192; // 8KB chunks
        
        while (!feof($fp)) {
            // Check memory usage
            if (memory_get_usage(true) > $memoryLimit) {
                dslog("Memory threshold reached. Garbage collecting...\n");
                gc_collect_cycles();
            }
            
            $buffer .= fread($fp, $chunk_size);
            $lines = explode("\n", $buffer);
            
            // Keep the last incomplete line in buffer
            $buffer = array_pop($lines);
            
            foreach ($lines as $line) {
                $csv_line = trim($line);
                if (strlen($csv_line) < 3) continue;
                
                // Process the line
                $result = processProductLine($csv_line, $datafeed, $fp_output);
                if ($result) {
                    $count++;
                } else {
                    $failed++;
                }
                
                // Update progress periodically
                if (($count % 100) == 0) {
                    dslog("Imported $count products for {$datafeed['merchant_name']}\n");
                    $datafeed['total_products'] = $count;
                    datafeed_update($datafeed);
                }
            }
        }
        
        // Process remaining buffer if not empty
        if (!empty($buffer)) {
            $result = processProductLine($buffer, $datafeed, $fp_output);
            if ($result) $count++;
        }
        
		gc_collect_cycles();
        // Clean up
        fclose($fp);
        fclose($fp_output);
        
        // Process final file
        $final_path = $DATAFILE_FOLDER."_amazon/".$datafeed['id'].".csv";
        $temp_path = $DATAFILE_FOLDER."_amazon/temp/".$datafile_output;
        
        // Remove duplicates efficiently
        $seen = array();
        $out = fopen($final_path, 'w');
        $in = fopen($temp_path, 'r');
        
        while (($line = fgets($in)) !== false) {
            $hash = md5($line);
            if (!isset($seen[$hash])) {
                fwrite($out, $line);
                $seen[$hash] = true;
            }
        }
        
        fclose($in);
        fclose($out);
        unlink($temp_path);
        
        return $count;
        
    } catch (Exception $e) {
        dslog("Error in parseApiFeed: " . $e->getMessage() . "\n");
        
        // Clean up resources
        if (isset($fp) && is_resource($fp)) fclose($fp);
        if (isset($fp_output) && is_resource($fp_output)) fclose($fp_output);
        
        throw $e;
    }
}

function processProductLine($csv_line, $datafeed, $fp_output) {
	$config['datafeed_id'] = $datafeed['id'];
	$config['access_key'] = $datafeed['amazon_access_key'];
	$config['secret_key'] = $datafeed['amazon_secret_key'];
	$config['associates_tag'] = $datafeed['amazon_associates_tag'];
	$config['endpoint'] = $datafeed['amazon_endpoint'];
	$config['host'] = $datafeed['amazon_host'];
	$config['region'] = $datafeed['amazon_region'];
	$config['currency_symbol'] = $datafeed['amazon_currency_symbol'];
	$config['nodeid_men'] = $datafeed['amazon_nodeid_men'];
	$config['nodeid_women'] = $datafeed['amazon_nodeid_women'];
	$config['extra_string'] = $datafeed['amazon_string'];
	$config['merchant_name'] = $datafeed['merchant_name'];
	$config['api_key'] = slug($datafeed['merchant_name']);
	$network = new Amazon($config);
    try {
        $options['keywords'] = strtolower($csv_line);
        $options['keywords'] = str_replace(
            ["<<men_string>>", "<<women_string>>"],
            [$config['nodeid_men'], $config['nodeid_women']],
            $options['keywords']
        );
        
        $products = $network->findProductsCSV($options);
        if (!isset($products['0'])) {
            api_search_error_insert($options['keywords'], $datafeed['id']);
            return false;
        }
        
        foreach ($products as $product) {
            if (!isset($product['display_price'])) continue;
            
            $fields = array(
                $product['name'],
                $product['network_product_key'],
                $product['image_url'],
                $product['deep_link'],
                $product['display_price']
            );
            
            fputcsv($fp_output, $fields);
            return true;
        }
        
    } catch (Exception $e) {
        dslog("Error processing line: " . $e->getMessage() . "\n");
        return false;
    }
    
    return false;
}

function fetchApiFeed($datafeed) {
	global $DATAFILE_FOLDER;
	global $PHP_SHELL_COMMAND;
	global $SCRIPT_FOLDER;
	global $CATEGORY_MATCHES;	
	global $SUB_CATEGORY_MATCHES;
		
	dslog("---------------------------------------------------------------\n");
	dslog(date('l jS \of F Y h:i:s A'));		
	dslog("Processing datafeed ID {$datafeed['id']} ({$datafeed['merchant_name']})\n");
	dslog("Processing datafeed product file ".$datafeed['amazon_product_list_file']."\n");

		$datafeed['status'] = "Downloading";
		datafeed_update($datafeed);
	
		dslog ("Feed Type is Webservice. Downloading produtct list file".$datafeed['amazon_product_list_file']."\n");
		$datafile = $datafeed['id'] . "-" . time() . ".dsf";
/****************** ERROR FILE WILL NEVER BE UPDATED EVEN IF DIFFERENT ************/
		/*
		if (file_exists("$DATAFILE_FOLDER/$datafile")) {
			if( time()-filemtime("$DATAFILE_FOLDER/$datafile") > 2 * 3600)
				$cache = false;
			else {
				$cache = true;
				dslog ("Reading from cache file $DATAFILE_FOLDER/$datafile\n");
				return 'cache';
			}
		}
		*/
		$cache = false;
		if(!$cache) {
			$ch = curl_init($datafeed['amazon_product_list_file'].'?nocache='.time());
			$fp = fopen("$DATAFILE_FOLDER/$datafile", "w");

			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); 
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt ($ch, CURLOPT_TIMEOUT, 300);

			curl_exec($ch);

			if(curl_errno($ch)) {
				dslog('WARNING! Curl error: #' . curl_errno($ch) . ' - '. curl_error($ch) . "\n");
				return false;
			}
				
			curl_close($ch);
			fclose($fp);   

			chmod("$DATAFILE_FOLDER/$datafile", 0777);
		}
		
	
	return $datafile;
}

function populateMatches($category_type) {
	$matches = array();	
	
	$category_matches_array = resultset_to_array(category_match_find_all($category_type));	
	foreach($category_matches_array as $match) {
		$matches[$match['match_content']] = $match;
	}
	
	return $matches;
}

/*
function parseFeed($datafeed, $datafile, $import_start_time) {
	global $DATAFILE_FOLDER;
	global $CATEGORY_MATCHES;
	global $SUB_CATEGORY_MATCHES;
	global $DS_IMPORT_DELETE_AFTER;
	
	$datafeed['status'] = "Importing";
	$datafeed['total_products'] = 0;
	datafeed_update($datafeed);
	
	$parser = parser_find($datafeed['parser_id']);
	$filter_result = filter_findby_parser_id($datafeed['parser_id']);	
	$filters = array();
	
	for ($i = 0; $i < $filter_result->num_rows; $i++) {

		$filter = $filter_result->fetch_array();	
		$field_array = $filters[$filter['field_name']];
		
		dslog ("Adding filter for {$filter['field_name']}\n");
		
		if (! $field_array) {
			dslog("No field array for {$filter['field_name']} - creating\n");
			$field_array = array();
			$field_array[$filter['id']] = $filter;
		}
		
		$field_array[$filter['id']] = $filter;
		$filters[$filter['field_name']] = $field_array;					
	}
		
	#var_dump($filters);
		
	$fp = fopen($DATAFILE_FOLDER . '/' . $datafile, "r");
	
	if (! $fp) {
		dslog("***********************************************************\n");
		dslog("WARNING - Could not open the feed file for reading!\n");
		dslog ("Import finished for {$datafeed['merchant_name']}. 0 products imported\n");
		$datafeed['status'] = "FAILED - NO FILE FOUND";
		$datafeed['total_products'] = 0;	
		$datafeed['last_import'] = date('Y-m-d H:i:s', time());
		datafeed_update($datafeed);
		dslog("***********************************************************\n");
		return;
	}
	
	if ($parser['skip_first_row'] == "on") {
		dslog("Skipping First Line\n");
		fgets($fp);
	}

	$count = 0;
	$failed = 0;
	
	$sep = str_replace('\t', "\t", $parser['parser_separator']);
		
	$CATEGORY_MATCHES = populateMatches(CATEGORY);
	$SUB_CATEGORY_MATCHES = populateMatches(SUB_CATEGORY);
		
	while ($csv_line = fgetcsv($fp, 10000, $sep)) {
		
		if (product_insert(create_product($csv_line, $datafeed, $parser, $filters), true)) {
			$count++;
		} else {
			$failed++;
		}

		if (($count % 100) == 0) {
			dslog("Imported $count products for {$datafeed['merchant_name']}\n");
			$datafeed['total_products'] = $count;
			datafeed_update($datafeed);
 		}
	}
	
	dslog ("Import finished for {$datafeed['merchant_name']}. $count products imported (and $failed failed to be imported)\n");
	$datafeed['status'] = "Imported";
	$datafeed['total_products'] = $count;	
	$datafeed['last_import'] = date('Y-m-d H:i:s', time());
	datafeed_update($datafeed);
	
	if (($DS_IMPORT_DELETE_AFTER) && ($datafeed['feed_type'] == 'automatic')) {
		dslog("Deleting feed file\n");
		unlink($DATAFILE_FOLDER . '/' . $datafile);
	}
	
}

*/

function parseApiFeed($datafeed, $datafile, $import_start_time) {
	global $db;
	global $DATAFILE_FOLDER;
	global $CATEGORY_MATCHES;
	global $SUB_CATEGORY_MATCHES;
	global $DS_IMPORT_DELETE_AFTER;
	global $MAXID;
	
	$config['datafeed_id'] = $datafeed['id'];
	$config['access_key'] = $datafeed['amazon_access_key'];
	$config['secret_key'] = $datafeed['amazon_secret_key'];
	$config['associates_tag'] = $datafeed['amazon_associates_tag'];
	$config['endpoint'] = $datafeed['amazon_endpoint'];
	$config['host'] = $datafeed['amazon_host'];
	$config['region'] = $datafeed['amazon_region'];
	$config['currency_symbol'] = $datafeed['amazon_currency_symbol'];
	$config['nodeid_men'] = $datafeed['amazon_nodeid_men'];
	$config['nodeid_women'] = $datafeed['amazon_nodeid_women'];
	$config['extra_string'] = $datafeed['amazon_string'];
	$config['merchant_name'] = $datafeed['merchant_name'];
	$config['api_key'] = slug($datafeed['merchant_name']);
	$network = new Amazon($config);
	
	//var_dump($datafeed);
	
	
	$datafeed['status'] = "Importing";
	$datafeed['total_products'] = 0;
	datafeed_update($datafeed);
	
		
	$fp = fopen($DATAFILE_FOLDER . '/' . $datafile, "r");
	
	$datafile_output = $datafeed['id'] . "-" . time() . ".dsf";
	$fp_output = fopen($DATAFILE_FOLDER."_amazon/temp/".$datafile_output, "w");
	
	if (! $fp) {
		dslog("***********************************************************\n");
		dslog("WARNING - Could not open the feed file for reading!\n");
		dslog ("Import finished for {$datafeed['merchant_name']}. 0 products imported\n");
		$datafeed['status'] = "FAILED - NO FILE FOUND";
		$datafeed['total_products'] = 0;	
		$datafeed['last_import'] = date('Y-m-d H:i:s', time());
		datafeed_update($datafeed);
		dslog("***********************************************************\n");
		return;
	}
	dslog("DATAFEED LOADED - ".$datafile."\n");
	
	$count = 0;
	$failed = 0;
	$MAXID = $MAXID+1;
	
	$csv_lines = explode(",", fread($fp, filesize($DATAFILE_FOLDER . '/' . $datafile)));
	dslog("FOUND - ".count($csv_lines)." PRODUCTS\n");
	
	foreach($csv_lines as $csv_line) {
		dslog("LINE - ".$csv_line."\n");
		if(strlen($csv_line) < 3)
			continue;
		/*
		if (product_insert(create_product($csv_line, $datafeed, $parser, $filters), true)) {
			$count++;
		} else {
			$failed++;
		}
		*/
		$options['keywords'] = strtolower($csv_line);
		$options['keywords'] = str_replace("<<men_string>>", $config['nodeid_men'], $options['keywords']);
		$options['keywords'] = str_replace("<<women_string>>", $config['nodeid_women'], $options['keywords']);
		//$options['keywords'] = strtolower($csv_line);
		
		dslog("KEYWORDS - ".$options['keywords']."\n");
		
		/*
		if(api_search_errors_alternative_keywords($options['keywords'], $datafeed['id']) != "")
			$options['keywords'] = api_search_errors_alternative_keywords($options['keywords'], $datafeed['id']);
		*/
		
/**** CACHE 
		if (($options['keywords']) && ( api_search_exists($options['keywords'], 8, $datafeed['id'] ) > 0)) {	
			$cache_product_found = false;
			dslog("Search already present in datafeed ".$datafeed['id']." cache ".$options['keywords']."\n\n");
			$sql = "insert into temp_products select * from products where datafeed_id = ".$datafeed['id']." and api_keywords = '".$options['keywords']."' ";
	
			//dslog($sql."\n");
			
			$result = $db->query($sql);
			
			
			if($result) {
				dslog("affected row".$db->affected_rows."\n");
				$count = $count +  $db->affected_rows;
				if($db->affected_rows > 0)
					$cache_product_found = true;
			}
			else {
				dslog("ERROR: ".$db->error." --- ".$sql."\n");
			}
			if (($count % 100) == 0) {
				dslog("Imported $count products for {$datafeed['merchant_name']}\n");
				$datafeed['total_products'] = $count;
				datafeed_update($datafeed);
			}
			if($cache_product_found) {
				continue;
			}
			else {
				dslog("Zero products found in cache. Searching again for: ".$datafeed['id']." cache ".$options['keywords']."\n\n");
				api_search_insert($options['keywords'], $datafeed['id']);
			}
		}
		else {
			dslog("Search NOT present in datafeed ".$datafeed['id']." cache ".$options['keywords']."\n\n");
			api_search_insert($options['keywords'], $datafeed['id']);
		}
 END CACHE ****/
		
		$options['nodeid'] = $config['nodeid_men'];
		
		$products = $network->findProductsCSV($options);
		dslog("Find products\n");
		if(isset($products['0'])) {
			
			foreach($products as $product) {
				//print_r($product['name']);
				$options['product_key'] = $product['product_key'];
				//$product = $network->getProduct($options);
				$product['autoslug'] = slug($product['name']);
				$product['created_at'] = date("Y-m-d H:i:s");
				$mysqldate = date('Y-m-d H:i:s', time());		
				$product['updated_at'] = $mysqldate;
				$product['api_keywords'] = strtolower($options['keywords']);
				$product['id'] = $MAXID;
				//product_insert($product);
				
				dslog("MAXID:".$count."\n");
				
				if(isset($product['display_price'])) {
					$fields = array($product['name'], $product['network_product_key'], $product['image_url'], $product['deep_link'], $product['display_price']);
					fputcsv($fp_output, $fields);
					dslog("written to CVS product name :".$product['name']."\n");
					gc_collect_cycles();
					ob_end_clean();
					dslog("Clean memory\n");
					$count++;
				}
				if (($count % 100) == 0) {
					dslog("Imported $count products for {$datafeed['id']}\n");
					$datafeed['total_products'] = $count;
					datafeed_update($datafeed);
				}
				/*
				if (strlen($product['name']) > 3 && product_insert($product, true)) {
					$count++;
					$MAXID++;
					dslog("Imported {$product['mykey']} products for {$datafeed['merchant_name']}\n");
				} else {
					$failed++;
				}
				*/
			}
		}
		else {
			api_search_error_insert($options['keywords'], $datafeed['id']);
		}
		
		//sleep(1);
	}
	
	// Clean up
	fclose($fp);
	fclose($fp_output);
	
	gc_collect_cycles();
	ob_end_clean();
	
	// remove duplicate lines
	//file_put_contents($DATAFILE_FOLDER."_amazon/".$config['datafeed_id'].".csv", array_unique(file($DATAFILE_FOLDER."_amazon/temp/".$datafile_output)));
	//unlink($DATAFILE_FOLDER."_amazon/temp/".$datafile_output);
	
	// Process final file
	$final_path = $DATAFILE_FOLDER."_amazon/".$datafeed['id'].".csv";
	$temp_path = $DATAFILE_FOLDER."_amazon/temp/".$datafile_output;
	
	// Remove duplicates efficiently
	$seen = array();
	$out = fopen($final_path, 'w');
	$in = fopen($temp_path, 'r');
	
	while (($line = fgets($in)) !== false) {
		$hash = md5($line);
		if (!isset($seen[$hash])) {
			fwrite($out, $line);
			$seen[$hash] = true;
		}
	}
	
	fclose($in);
	fclose($out);
	unlink($temp_path);
	
	
	
	dslog ("Import finished for {$datafeed['id']}. $count products imported (and $failed failed to be imported)\n");
	$datafeed['status'] = "Imported";
	$datafeed['total_products'] = $count;	
	$datafeed['last_import'] = date('Y-m-d H:i:s', time());
	if($count < 200) {
		$headers = 'From: root@dfsstaging.shoeprices.info' . "\r\n" .
		'Reply-To: root@dfsstaging.shoeprices.info' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
		mail("root@dfsstaging.shoeprices.info", $datafeed['merchant_name']." products low count", "Cron results:\r\nLess then 200 products found for ".$datafeed['merchant_name'], $headers);
	}
	datafeed_update($datafeed);
	
}

function filter_replace($value, $param1, $param2) {
	return str_replace($param1, $param2, $value);
}

function filter_buyatstock($value) {
	return (($value == 'In Stock') || ($value == '1') || ($value == '') || ($value == 'Y'));
}

function set_value($product, $parser, $csv_line, $filters, $field) {

	$field_filters = $filters[$field];
	#dslog "Found " . sizeof($field_filters) . " matching filters for '$field'\n";
	
	if ($field_filters) {
		$value = $csv_line[$parser[$field]];
		
		#dslog "Old value '$value'\n";
		
		foreach ($filters[$field] as $filter) {
			#dslog "{$filter['id']} - {$filter['filter_type']}\n";
			
			if ($filter['filter_type'] == 'replace') {
				$value = filter_replace($value, $filter['param1'], $filter['param2']);
			} else if ($filter['filter_type'] == 'buyatstock') {
				$value = filter_buyatstock($value);
			} else if ($filter['filter_type'] == 'porstock') {
				$value = 1;
			} else if ($filter['filter_type'] == 'append') {
				$value = $value . $filter['param1'];
			} else if ($filter['filter_type'] == 'prepend') {
				$value = $filter['param1'] . $value;
			}
		}

		#dslog "New value '$value'\n";
	} else {
		$value = $csv_line[$parser[$field]];
	}
	
	#dslog "$field = '$value'\n";
	$product[$field] = $value;
	return $product;
}

function set_product_category($product, $category_type, &$category_matches) {
	
	if ($category_type == CATEGORY) {
		$category_name = $product['category'];
		$category_key = 'category_id';
	} elseif ($category_type == SUB_CATEGORY) {
		$category_name = $product['subcategory'];
		$category_key = 'sub_category_id';
	} else {
		safe_die("Unknown Category Type : $category_type");
	}

	if (empty($category_name)) {
		return -1;
	}

	//dslog("Category = $category_name\n");
	
	$existing_match = $category_matches[strtolower($category_name)];
	
	if ($existing_match) {		
		//dslog("Found a match for $category_name \n");
		return $existing_match['category_id'];
	} else {
		//dslog("Creating new category for $category_name\n");
		
		// create new category
		$category = array();
		$category['name'] = $category_name;
		$category['cat_type'] = 'auto';
		
		if ($category_type == SUB_CATEGORY) {
			//dslog("Sub Cat - setting parent id to " . $product['category_id'] . "\n");
			$category['parent_category_id'] = $product['category_id'];
		}
		
		$category_id = category_insert($category);
		//dslog("New cat ID = " . $category_id . "\n");
		
		// create new match
		$match = array();
		$match['category_id'] = $category_id;
		$match['match_content'] = strtolower($category_name);
		$match['category_type'] = $category_type;
		category_match_insert($match);
		$category_matches[strtolower($category_name)] = $match;
		return $category_id;
	}	
}


function create_product($csv_line, $datafeed, $parser, $filters) {		
	
	global $CATEGORY_MATCHES;
	global $SUB_CATEGORY_MATCHES;
	
	$product = array();

	$product = set_value($product, $parser, $csv_line, $filters, 'category');
	$product = set_value($product, $parser, $csv_line, $filters, 'subcategory');
	$product = set_value($product, $parser, $csv_line, $filters, 'model_number');
	$product = set_value($product, $parser, $csv_line, $filters, 'name');
	$product = set_value($product, $parser, $csv_line, $filters, 'description');		
	$product = set_value($product, $parser, $csv_line, $filters, 'thumb_url');		
	$product = set_value($product, $parser, $csv_line, $filters, 'image_url');	
	$product = set_value($product, $parser, $csv_line, $filters, 'deep_link');	
	$product = set_value($product, $parser, $csv_line, $filters, 'delivery_time');	
	$product = set_value($product, $parser, $csv_line, $filters, 'in_stock');											
	$product = set_value($product, $parser, $csv_line, $filters, 'rrp_price');		
	$product = set_value($product, $parser, $csv_line, $filters, 'display_price');		
	$product = set_value($product, $parser, $csv_line, $filters, 'is_preorder');		
	$product = set_value($product, $parser, $csv_line, $filters, 'delivery_cost');		
	$product = set_value($product, $parser, $csv_line, $filters, 'mykey');		
	$product = set_value($product, $parser, $csv_line, $filters, 'userfield1');		
	$product = set_value($product, $parser, $csv_line, $filters, 'userfield2');		
	$product = set_value($product, $parser, $csv_line, $filters, 'userfield3');		
	$product = set_value($product, $parser, $csv_line, $filters, 'userfield4');		
		
	$product['merchant_name'] = $datafeed['merchant_name'];
	$product['mykey'] = $datafeed['id'] . "_" . slug($product['mykey']);
	$product['datafeed_id'] = $datafeed['id'];
	
	
	if ($parser['in_stock'] == -1) {
		$product['in_stock'] = -1;
	}
	
	$mysqldate = date('Y-m-d H:i:s', time());		
	$product['updated_at'] = $mysqldate;
	$product['created_at'] = $mysqldate;
	
	$product['autoslug'] = slug($product['name']);
	
	
	$product['category_id'] = set_product_category($product, CATEGORY, $CATEGORY_MATCHES);
	$product['sub_category_id'] = 
		set_product_category($product, SUB_CATEGORY, $SUB_CATEGORY_MATCHES);
		
	return $product;
}

function processFeeds($datafeed_result) {
	for ($i = 0; $i < $datafeed_result->num_rows; $i++) {
			processFeed($datafeed_result->fetch_array());		
	}	
}



function updatePrices($datafeed) {
	global $PHP_SHELL_COMMAND;
	
	$datafeed['status'] = "Refreshing Prices";
	datafeed_update($datafeed);
		
	dslog("Updating prices $PHP_SHELL_COMMAND '../bin/apirefresh.php'\n");
/****************** ERROR LOG FOLDER NOT DEFINED ************/
	$LOG_FILE = "$LOG_FOLDER/{$datafeed['id']}.$LOG_EXTENSION";
	//dslog($LOG_FILE.'\n');
	//system("$PHP_SHELL_COMMAND '../bin/apirefresh.php' '{$datafeed['id']}' > '$LOG_FILE' &");
	system("$PHP_SHELL_COMMAND '../bin/apirefresh.php' '{$datafeed['id']}'");
}

function processFeed($datafeed) {
	//if($datafeed['id'] == 62){
		
			$datafile = fetchApiFeed(datafeed_find($datafeed['id']));
			if($datafile != 'cache' && $datafile != false)
				parseApiFeed($datafeed, $datafile, $import_start_time);
	//}
}

// ****************************************************************************
// Main
// ****************************************************************************

if (file_exists($LOCK_FILE_CSV)) {
	print "An import is already in progress so I wont run again. If you think this is\n";
	print " a mistake the delete the file : \n\n$LOCK_FILE_CSV\n\n and run this script again.\n";
	
	$headers = 'From: warning@dfs.runningshoesguru.com' . "\r\n" .
    'Reply-To: warning@dfs.runningshoesguru.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
	
	//mail("root@localhost",'DFS Lockfile warning','DFS Lockfile warning', $headers);
	
	die;
}

createLogFile($LOG_FILE);

dslog(">>> DS import.php process started\n");
register_shutdown_function('shutdown');

chdir("$INSTALL_FOLDER/admin/bin");

if ($argc != 2) {
	print "Usage: import.php [datafeed_id|all|datafeed_id_1,datafeed_id_2,...|a-z]\n";
	die;
}

$import_start_time = time();
if (! touch($LOCK_FILE_CSV)) {
  dslog("WARNING: Couldn't create lock file\n");
}

/*
if ($argv[1] == "all") {
	$datafeed_result = datafeed_find_all_ws();
	for ($i = 0; $i < $datafeed_result->num_rows; $i++) {
		updatePrices($datafeed_result->fetch_array());		
	}
} else {
	updatePrices(datafeed_find($argv[1]));
}
*/

if ($argv[1] == "all") {
	while(true){
		createLogFile($LOG_FILE);
		dslog("ALL Feed Mode - STARTINGG\n");
		processFeeds(datafeed_find_all());
		dslog("Sleep 21600\n");
		sleep(21600);
	}
} 
elseif ($argv[1] == "test") {
	file_put_contents($DATAFILE_FOLDER."_amazon/newdata.csv", array_unique(file($DATAFILE_FOLDER."_amazon/temp/54-1600865753.dsf")));
}
else {
	dslog("Single Feed Mode\n");	
	processFeed(datafeed_find($argv[1]));
}


unlink($LOCK_FILE_CSV);
dslog("<<< DS import.php process finished\n");

?>
