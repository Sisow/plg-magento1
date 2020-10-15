<?php

class Sisow_Model_Base extends Mage_Core_Model_Abstract
{
    protected static $issuers;
    protected static $lastcheck;

    private $response;

    // Merchant data
    private $merchantId;
    private $merchantKey;
    private $shopId;

    // Transaction data
    public $payment;    // empty=iDEAL; sofort=DIRECTebanking; mistercash=MisterCash; ...
    public $issuerId;    // mandatory; sisow bank code
    public $purchaseId;    // mandatory; max 16 alphanumeric
    public $entranceCode;    // max 40 strict alphanumeric (letters and numbers only)
    public $description;    // mandatory; max 32 alphanumeric
    public $amount;        // mandatory; min 0.45
    public $notifyUrl;
    public $returnUrl;    // mandatory
    public $cancelUrl;
    public $callbackUrl;

    // Status data
    public $status;
    public $timeStamp;
    public $consumerAccount;
    public $consumerBic;
    public $consumerIban;
    public $consumerName;
    public $consumerCity;
    
    // Invoice data
    public $invoiceNo;
    public $documentId;
    public $documentUrl;

    // Result/check data
    public $trxId;
    public $issuerUrl;

    // Error data
    public $errorCode;
    public $errorMessage;

    // Status
    const statusSuccess = "Success";
    const statusCancelled = "Cancelled";
    const statusExpired = "Expired";
    const statusFailure = "Failure";
    const statusOpen = "Open";

    private $storeId;

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->storeId = Mage::app()->getStore()->getStoreId();
        //$this->_init('sisow/base');
        $this->merchantId = Mage::getStoreConfig('sisow_core/merchantid',$this->storeId);
        $this->merchantKey = Mage::getStoreConfig('sisow_core/merchantkey',$this->storeId);
        $this->shopId = Mage::getStoreConfig('sisow_core/shopid',$this->storeId);
    }

    public function switchStore($storeId) {
        $this->storeId = $storeId;
        //$this->_init('sisow/base');
        $this->merchantId = Mage::getStoreConfig('sisow_core/merchantid',$this->storeId);
        $this->merchantKey = Mage::getStoreConfig('sisow_core/merchantkey',$this->storeId);
        $this->shopId = Mage::getStoreConfig('sisow_core/shopid',$this->storeId);
    }

    private function error() 
    {
        $this->errorCode = $this->parse("errorcode");
        $this->errorMessage = urldecode($this->parse("errormessage"));
    }

    private function parse($search, $xml = false) 
    {
        if ($xml === false) {
            $xml = $this->response;
        }

        if (($start = strpos($xml, "<" . $search . ">")) === false) {
            return false;
        }

        $start += strlen($search) + 2;
        if (($end = strpos($xml, "</" . $search . ">", $start)) === false) {
            return false;
        }

        return substr($xml, $start, $end - $start);
    }

    public function send($method, array $keyvalue = NULL, $return = 1) 
    {
        $url = "https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/" . $method;
		
		$logging = Mage::getStoreConfig('sisow_core/debug',$this->storeId);
				
		if($logging){
			Mage::log('URL: ' . $url, null, 'sisow.log', true);
			Mage::log('PARAMS: ' . ($keyvalue == NULL ? "" : http_build_query($keyvalue, '', '&')), null, 'sisow.log', true);
		}
		
        $options = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => $return,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POSTFIELDS => $keyvalue == NULL ? "" : http_build_query($keyvalue, '', '&'));
			
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $this->response = curl_exec($ch);
        curl_close($ch); 
        if (!$this->response) {
			if($logging){
				Mage::log('RESPONSE: No response', null, 'sisow.log', true);
			}
            return false;
        }
		
		if($logging){
			Mage::log('RESPONSE: ' . $this->response, null, 'sisow.log', true);
		}
		
        return true;
    }

    private function getDirectory() 
    {
        $diff = 24 * 60 *60;
        if (self::$lastcheck)
            $diff = time() - self::$lastcheck;
        if ($diff < 24 *60 *60)
            return 0;
        if (!$this->send("DirectoryRequest"))
            return -1;
        $search = $this->parse("directory");
        if (!$search) {
            $this->error();
            return -2;
        }

        self::$issuers = array();
        $iss = explode("<issuer>", str_replace("</issuer>", "", $search));
        foreach ($iss as $k => $v) {
            $issuerid = $this->parse("issuerid", $v);
            $issuername = $this->parse("issuername", $v);
            if ($issuerid && $issuername) {
                self::$issuers[$issuerid] = $issuername;
            }
        }

        self::$lastcheck = time();
        return 0;
    }

    // DirectoryRequest
    public function DirectoryRequest(&$output, $select = false, $test = false) 
    {
        if ($test === true) {
            // kan ook via de gateway aangevraagd worden, maar is altijd hetzelfde
            if ($select === true) {
                $output = "<select id=\"sisowbank\" name=\"issuerid\">";
                $output .= "<option value=\"99\">Sisow Bank (test)</option>";
                $output .= "</select>";
            }
            else {
                $output = array("99" => "Sisow Bank (test)");
            }

            return 0;
        }

        $output = false;
        $ex = $this->getDirectory();
        if ($ex < 0) {
            return $ex;
        }

        if ($select === true) {
            $output = "<select id=\"sisowbank\" name=\"issuerid\">";
        }
        else {
            $output = array();
        }

        foreach (self::$issuers as $k => $v) {
            if ($select === true) {
                $output .= "<option value=\"" . $k . "\">" . $v . "</option>";
            }
            else {
                $output[$k] = $v;
            }
        }

        if ($select === true) {
            $output .= "</select>";
        }

        return 0;
    }

    // TransactionRequest
    public function TransactionRequest($keyvalue = NULL) 
    {
        $this->trxId = $this->issuerUrl = "";
        if (!$this->merchantId) {
            $this->errorMessage = 'No Merchant ID';
            return -1;
        }

        if (!$this->merchantKey) {
            $this->errorMessage = 'No Merchant Key';
            return -2;
        }

        if (!$this->purchaseId) {
            $this->errorMessage = 'No purchaseid';
            return -3;
        }

        if ($this->amount < 0.45) {
            $this->errorMessage = 'Amount < 0.45';
            return -4;
        }

        if (!$this->description) {
            $this->errorMessage = 'No description';
            return -5;
        }

        if (!$this->returnUrl) {
            $this->errorMessage = 'No returnurl';
            return -6;
        }

        if (!$this->issuerId && !$this->payment) {
            $this->errorMessage = 'No issuerid or no payment method';
            return -7;
        }
        
        $productId = 1;
		$productTotal = 0;
		
		while(array_key_exists('product_total_' . $productId, $keyvalue)){
			$productTotal += $keyvalue['product_total_' . $productId];
			$productId++;
		}
		
		if($productTotal != ($this->amount * 100)){
			$priceDifferent = ($this->amount * 100) - $productTotal;
			
			$keyvalue['product_id_' . $productId] = 'correctie';
			$keyvalue['product_description_' . $productId] = 'Correctieregel';
			$keyvalue['product_quantity_' . $productId] = 1;
			$keyvalue['product_weight_' . $productId] = 0;
			$keyvalue['product_tax_' . $productId] = 0;
			$keyvalue['product_taxrate_' . $productId] = 0;
			$keyvalue['product_netprice_' . $productId] = $priceDifferent;
			$keyvalue['product_nettotal_' . $productId] = $priceDifferent;
			$keyvalue['product_total_' . $productId] = $priceDifferent;
		}
		        
        if (!$this->entranceCode)
            $this->entranceCode = $this->purchaseId;
        
        $pars = array();
        
        if(strlen($keyvalue['billing_countrycode']) == 2)
            $pars["locale"] = $this->setLocale($keyvalue['billing_countrycode']);
        else
            $pars["locale"] = $this->setLocale("");
        
        $pars["merchantid"] = $this->merchantId;
        $pars["shopid"] = $this->shopId;
        $pars["payment"] = $this->payment;
        $pars["issuerid"] = $this->issuerId;
        $pars["purchaseid"] = $this->purchaseId; 
        $pars["amount"] = round($this->amount * 100);
        $pars["description"] = $this->description;
        $pars["entrancecode"] = $this->entranceCode;
        $pars["returnurl"] = $this->returnUrl;
        $pars["cancelurl"] = $this->cancelUrl;
        $pars["callbackurl"] = $this->callbackUrl;
        $pars["notifyurl"] = $this->notifyUrl;

        $pars["sha1"] = sha1($this->purchaseId . $this->entranceCode . round($this->amount * 100)  . $this->shopId . $this->merchantId . $this->merchantKey);
        if ($keyvalue) {
            foreach ($keyvalue as $k => $v) {
                $pars[$k] = $v;
            }
        }

        if (!$this->send("TransactionRequest", $pars))
            return -8;
        $this->trxId = $this->parse("trxid");
        $this->issuerUrl = urldecode($this->parse("issuerurl"));
        $this->invoiceNo = $this->parse("invoiceno");
        $this->documentId = $this->parse("documentid");
        $this->documentUrl = $this->parse("documenturl");
        if (!$this->issuerUrl) {
            $this->error();
            return -9;
        }

        return 0;
    }

    // StatusRequest
    public function StatusRequest($trxid = false) 
    {
        if ($trxid === false)
            $trxid = $this->trxId;
        if (!$this->merchantId)
            return -1;
        if (!$this->merchantKey)
            return -2;
        if (!$trxid)
            return -3;
        $this->trxId = $trxid;
        $pars = array();
        $pars["merchantid"] = $this->merchantId;
        $pars["shopid"] = $this->shopId;
        $pars["trxid"] = $this->trxId;
        $pars["sha1"] = sha1($this->trxId . $this->shopId . $this->merchantId . $this->merchantKey);
        if (!$this->send("StatusRequest", $pars))
            return -4;
        $this->status = $this->parse("status");
        if (!$this->status) {
            $this->error();
            return -5;
        }

        $this->timeStamp = $this->parse("timestamp");
        $this->amount = $this->parse("amount") / 100.0;
        $this->consumerAccount = $this->parse("consumeraccount");
        $this->consumerBic = $this->parse("consumerbic");
        $this->consumerIban = $this->parse("consumeriban");
        $this->consumerName = $this->parse("consumername");
        $this->consumerCity = $this->parse("consumercity");
        $this->purchaseId = $this->parse("purchaseid");
        $this->description = $this->parse("description");
        $this->entranceCode = $this->parse("entrancecode");
        return 0;
    }

    // RefundRequest
    public function RefundRequest($trxid) 
    {
        $pars = array();
        $pars["merchantid"] = $this->merchantId;
        $pars["trxid"] = $trxid;
        if($this->amount > 0)
            $pars["amount"] = round($this->amount * 100.0);
        $pars["sha1"] = sha1($trxid . $this->merchantId . $this->merchantKey);
        if (!$this->send("RefundRequest", $pars))
            return -1;
        $this->documentId = $this->parse("refundid");
        if (!$this->documentId) {
            $this->error();
            return -2;
        }

        return $this->documentId;
    }
    
    // checkMerchant
    public function checkMerchant() 
    {
        if (!$this->merchantId)
            return -1;
        if (!$this->merchantKey)
            return -2;
        $pars = array();
        $pars["merchantid"] = $this->merchantId;
        $pars["sha1"] = sha1($this->merchantId . $this->merchantKey);
        
        if (!$this->send("CheckMerchantRequest", $pars))
            return -4;
        $this->timeStamp = $this->parse("timestamp");
        $this->active = $this->parse("active");
        return 0;
    }

    // InvoiceRequest
    public function InvoiceRequest($trxid, $keyvalue = NULL) 
    {
        $pars = array();
        $pars["merchantid"] = $this->merchantId;
        $pars["trxid"] = $trxid;
        $pars["sha1"] = sha1($trxid . $this->merchantId . $this->merchantKey);
        if ($keyvalue) {
            foreach ($keyvalue as $k => $v) {
                $pars[$k] = $v;
            }
        }

        if (!$this->send("InvoiceRequest", $pars))
            return -1;
        $this->invoiceNo = $this->parse("invoiceno");
        $this->documentUrl = $this->parse("documenturl");

        if (!$this->invoiceNo) {
            $this->error();
            return -2;
        }
                
        $this->documentId = $this->parse("documentid");
        return 0; //$this->invoiceNo;
    }

    // CancelReservationRequest
    public function CancelReservationRequest($trxid) 
    {
        $pars = array();
        $pars["merchantid"] = $this->merchantId;
        $pars["trxid"] = $trxid;
        $pars["sha1"] = sha1($trxid . $this->merchantId . $this->merchantKey);
        if (!$this->send("CancelReservationRequest", $pars))
            return -1;
        return 0;
    }

    // CreditInvoiceRequest
    public function CreditInvoiceRequest($trxid, $keyvalue = NULL) 
    {
        $pars = array();
        $pars["merchantid"] = $this->merchantId;
        $pars["trxid"] = $trxid;
        $pars["sha1"] = sha1($trxid . $this->merchantId . $this->merchantKey);
        if ($keyvalue) {
            foreach ($keyvalue as $k => $v) {
                $pars[$k] = $v;
            }
        }

        if (!$this->send("CreditInvoiceRequest", $pars))
            return -1;
        $this->invoiceNo = $this->parse("invoiceno");
        $this->documentUrl = $this->parse("documenturl");
        if (!$this->invoiceNo) {
            $this->error();
            return -2;
        }

        $this->documentId = $this->parse("documentid");
        return 0; //$this->invoiceNo;
    }
    
    private function verifyOrder($pars)
    {
        if(!array_key_exists('product_total_1', $pars))
        {
            $this->errorCode = "ORDERROWS";
            $this->errorMessage = "No orderrows found";
            return false;
        }
        
        $rowtotal = 0;
        
        foreach($pars as $key => $value)
        {
            if (strpos($key, 'product_total_') !== false) {
                $rowtotal += $pars[$key];
            }
        }
        
        $diff = $rowtotal - round(($this->amount * 100.0));
        if($diff < 0)
            $diff = $diff * -1;

        if($diff > 1)
        {
            $this->errorCode = "ORDERTOTAL";
            $this->errorMessage = "Order total(".($this->amount * 100.0).") don't match RowTotal (".$rowtotal.")";
            return false;
        }
        
        return true;
    }
    
    public function setLocale($countryIso)
    {
        $supported = array("US");
        
        switch($this->payment)
        {
            case "paypalec":
                $supported = array('AU','AT','BE','BR','CA','CH','CN','DE','ES','GB','FR','IT','NL','PL','PT','RU','US');
                break;
            case "mistercash":
                $supported = array('NL', 'BE', 'DE', 'IT', 'ES', 'PT', 'BR', 'SE', 'FR');
                break;
            case "creditcard":
                $supported = array('NL', 'BE', 'DE', 'IT', 'ES', 'PT', 'BR', 'SE', 'FR');
                break;
            case "maestro":
                $supported = array('NL', 'BE', 'DE', 'IT', 'ES', 'PT', 'BR', 'SE', 'FR');
                break;
            case "mastercard":
                $supported = array('NL', 'BE', 'DE', 'IT', 'ES', 'PT', 'BR', 'SE', 'FR');
                break;
            case "visa":
                $supported = array('NL', 'BE', 'DE', 'IT', 'ES', 'PT', 'BR', 'SE', 'FR');
                break;
            default:
                return "NL";
                break;
        }
        
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        $lang = strtoupper($lang);
        
        $lang = (!isset($lang) || $lang == "") ? $countryIso : $lang;
        
        if($lang == "")
            return "US";
        if(in_array($lang, $supported))
            return $lang;
        else
            return 'US';
    }
}
