<?php

ini_set('include_path', dirname(dirname(dirname(__FILE__))) . '/lib/phpseclib/');

include('Net/SFTP.php');

class AcommerceScpModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $sftp = new Net_SFTP(Configuration::get('SECUREHOST'), Configuration::get('SECUREPORT'));
        
        if (!$sftp->login(Configuration::get('USERNAME'), Configuration::get('PASSWORD'))) {
            exit('Login Failed');
        }else{

            $this->upload($sftp);
        }
    }

    public function upload($sftp)
    {
        $this->fetchInfo(); die();

        if (file_exists( $file = dirname(dirname(dirname(__FILE__))) . "/files/hello.txt")) {
            $result = $sftp->put(Configuration::get('REMOTEDIR').'/hello.txt', $file, NET_SFTP_LOCAL_FILE | NET_SFTP_RESUME);
            var_dump($result); die('Success');
        }
    }

    public function fetchInfo()
    {
        $result = '';

        $query = '
            SELECT  o.reference AS "Order #", 
                    o.date_add AS "Order Date", 
                    c.email AS "Email", 
                    a.address1 as "Shipping Address Line 1",
                    a.address2 as "Shipping Address Line 2",
                    a.city as "Shipping Address City",
                    a.city as "Shipping Address State/Province",
                    ctl.name as "Shipping Address Country",
                    a.postcode as "Shipping Address Postal Code",
                    CONCAT(a.phone_mobile, " | ", a.phone) as "Shipping Address Phone",
                    o.id_order AS "Item  ID",
                    (SELECT count(*) FROM ps_order_detail AS od WHERE od.id_order = o.id_order ) AS "Item Qty",
                    o.total_paid AS "Gross Total",
                    op.payment_method AS "Payment Type",
                    cr.name AS "Shipping Type"
            FROM ps_orders AS o
            LEFT JOIN ps_order_carrier AS oc ON oc.id_carrier = o.id_carrier
            LEFT JOIN ps_carrier AS cr ON cr.id_carrier = oc.id_carrier
            LEFT JOIN ps_order_payment AS op ON op.order_reference = o.reference
            LEFT JOIN ps_address AS a ON a.id_address = o.id_address_delivery 
            LEFT JOIN ps_country AS ct ON ct.id_country = a.id_country
            LEFT JOIN ps_country_lang AS ctl ON ctl.id_country = ct.id_country 
            LEFT JOIN ps_customer AS c ON c.id_customer  = a.id_customer
            GROUP BY o.reference
        ';

        if($result =  Db::getInstance()->executeS($query))
        {
            $this->convertToCSV($result, 'CM'.date('Ymd001'));
        }
    }

    public function convertToCSV($results, $filename)
    {
        if (!$results) die('Couldn\'t fetch records');

        #fputcsv($fp, array_keys(reset($result)));
        $csv = implode(",", array_keys(reset($results)));
        for ($i=0; $i < sizeof($results); $i++) { 
            $csv.= $record[$i].','.$record[1]."\n"; //Append data to csv
        }

        $csv_handler = fopen ('csvfile.csv','w');
        fwrite ($csv_handler,$csv);
        fclose ($csv_handler);        

/*        ob_start();
        $fp = fopen('php://output', 'w');
        if ($fp && $result) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            fputcsv($fp, array_keys(reset($result)));
                foreach ($result as $item) {
                    fputcsv($fp, $item);
                }
            fclose($fp);
        }*/
    }   
}