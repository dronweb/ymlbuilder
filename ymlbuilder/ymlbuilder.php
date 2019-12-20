<?php
/**
 * @name YMLBuilder
 * @description Клас для создания yml-файла выгрузки для Яндекса
 * @class_uri:  Ссылка на инфо о плагине
 * @author_uri:  https://t.me/ivan_peshko/
 * @author Ivan Peshko
 * @version 1.0
 *
 */
class YMLBuilder {

    private $error = false;
    private $error_massive;
    private $eol = "\r\n";
    //private $header = "";

    private $xml_version = "1.0";
    private $encoding    = "UTF-8";
    private $name        ;
    private $company     ;
    private $main_url    ;
    private $email       ;
    private $date        ;
    private $currencies  ;
    private $categories  ;
    private $deliveries  ;
    private $offers      ;

    public $yml;

    public function __construct() {
        $this->date = date("Y-m-d H:i");
    }
    /**
     * @param string $version версия XML-файла
     */
    public function setXMLVersion($version) {
        $this->xml_version = $version;
    }
    /**
     * @param string $encoding Кодировка
     */
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function setCompany($company) {
        $this->company = $company;
    }
    public function setMainUrl($main_url) {
        $this->main_url = $main_url;
    }
    public function setEmail($email) {
        $this->email = $email;
    }
    public function addCurrencies($currencies){
        if ($currencies)
            foreach ($currencies as $currency)
                $this->currencies[] = array(
                    "id" => $currency['id'],
                    "rate" => $currency['rate']
                );
    }
    public function addCategories($categories){
        if ($categories)
            foreach ($categories as $category)
                $this->categories[] = array(
                    "id" => $category['id'],
                    "parent_id" => $category['parent_id'],
                    "name"=> $category['name']
                );
    }
    public function addDeliveries($deliveries){
        if ($deliveries)
            foreach ($deliveries as $delivery)
                $this->deliveries[] = array(
                    "cost" => $delivery['cost'],
                    "days" => $delivery['days'],
                    "order-before" => $delivery['order-before']
                );
    }
    public function addOffers($offers){
        if ($offers)
            foreach ($offers as $offer){
                if ($offer['name']!=''||$offer['id']!=''||$offer['url']!=''||$offer['price']!=''||
                    $offer['currencyId']!=''||$offer['categoryId']!=''||$offer['picture']!=''){
                    $this->offers[] = array(
                        "id" => $offer['id'],
                        'type'=> $offer['type'],
                        'bid'=> $offer['bid'],
                        'typePrefix'=> $offer['typePrefix'],
                        'name'=> $offer['name'],
                        'vendor'=> $offer['vendor'],
                        'url'=> $offer['url'],
                        'price'=> $offer['price'],
                        'currencyId' => $offer['currencyId'],
                        'categoryId' => $offer['categoryId'],
                        'picture' => $offer['picture'],
                        'store' => $offer['store'],
                        'pickup' => $offer['pickup'],
                        'delivery' => $offer['delivery'],
                        'delivery-options' => $offer['delivery-options'],
                        'description' => $offer['description']!='' ? '<![CDATA['.$offer['description'].']]>' : '',
                        'param' => $offer['param'],
                        'sales_notes' => $offer['sales_notes'],
                        'manufacturer_warranty' => $offer['manufacturer_warranty'],
                        'country_of_origin' => $offer['country_of_origin'],
                        'barcode' => $offer['barcode']
                    );
                }else{
                    $this->error = true;
                    $this->error_massive[] = array(
                        'type'=> 'addProduct',
                        'value'=> 'Error in '.$offer['id']
                    );
                }
            }
    }
    private function buildCurrency(){
        $eol = $this->eol;
        $currencies = $this->currencies;
        $string ='';
        foreach ($currencies as $currency){
            $string .= '<currency id="'.$currency['id'].'" rate="'.$currency['rate'].'"/>'.$eol;
        }
        return $string;
    }
    private function buildCategories(){
        $eol = $this->eol;
        $categories = $this->categories;
        $string ='';
        foreach ($categories as $category){
            $parent = $category['parent_id'] != '' ? ' parentId="'.$category['parent_id'].'"' : '' ;
            $string .= '<category id="'.$category['id'].'"'. $parent .'>'.$category['name'].'</category>'.$eol;
        }
        return $string;
    }

    private function buildDeliveryOptions(){
        $eol = $this->eol;
        $deliveries = $this->deliveries;
        $del ='';
        if ($deliveries != '') {
            foreach ($deliveries as $delivery){
                $offer = $delivery['order-before'] ? ' order-before="' . $delivery['order-before'] . '"' : '';
                $del .= '<option cost="'.$delivery['cost'].'" days="'.$delivery['days'].'"'.$offer.'/>'.$eol;
            }
        }
        return $del;
    }

    private function buildOffers(){
        $eol = $this->eol;
        $offers = $this->offers;
        $string ='';
        foreach ($offers as $offer){
            $bid = $offer['bid']!=''? ' bid="'.$$offer['bid'].'"':'';
            $string .= '<offer id="'.$offer['id'].'"'.$bid.'>'.$eol;
            $string .= '<name>'.$offer['name'].'</name>'.$eol;
            if ($offer['typePrefix']) $string .= '<typePrefix>'.$offer['typePrefix'].'</typePrefix>'.$eol;
            if ($offer['vendor']) $string .= '<vendor>'.$offer['vendor'].'</vendor>'.$eol;
            $string .= '<url>'.$offer['url'].'</url>'.$eol;
            $string .= '<price>'.$offer['price'].'</price>'.$eol;
            $string .= '<currencyId>'.$offer['currencyId'].'</currencyId>'.$eol;
            $string .= '<categoryId>'.$offer['categoryId'].'</categoryId>'.$eol;
            foreach ($offer['picture'] as $picture_url) if ($picture_url!='') $string .= '<picture>'.$picture_url.'</picture>'.$eol;
            !$offer['store'] ? $string .= '<store>false</store>'.$eol : $string .= '<store>true</store>'.$eol;
            !$offer['pickup'] ? $string .= '<pickup>false</pickup>'.$eol : $string .= '<pickup>true</pickup>'.$eol;
            !$offer['delivery'] ? $string .= '<delivery>false</delivery>'.$eol : $string .= '<delivery>true</delivery>'.$eol;
            if ($offer['delivery'] == true && $offer['delivery-options']!= ''){
                foreach ($offer['delivery-options'] as $options) {
                    $ob = $options['order-before']!='' ? ' order-before="'.$options['order-before'].'"' : '';
                    $string .= '<option cost="'.$options['cost'].'" days="'.$options['days'].'"'.$ob.'/>'.$eol;
                }
            }
            if ($offer['description']) $string .= '<description>'.$offer['description'].'</description>'.$eol;
            if ($offer['param']!='')
                foreach ($offer['param'] as $param) {
                    $unit = $param['unit']!=''? ' unit="'.$param['unit'].'"':'';
                    $string .= '<param name="'.$param['name'].'"'.$unit.'>'.$param['value'].'</param>'.$eol;
                }
            if ($offer['sales_notes']!='') $string .= '<sales_notes>'.$offer['sales_notes'].'</sales_notes>'.$eol;
            if ($offer['manufacturer_warranty']!='') $string .= '<manufacturer_warranty>'.$offer['manufacturer_warranty'].'</manufacturer_warranty>'.$eol;
            if ($offer['country_of_origin']) $string .= '<country_of_origin>'.$offer['country_of_origin'].'</country_of_origin>'.$eol;
            if ($offer['barcode']!='') $string .= '<barcode>'.$offer['currencyId'].'</barcode>'.$eol;

            $string .= ' </offer>'.$eol;

        }
        return $string;
    }

    private function check(){
        return true;
    }

    public function buildYml () {
        if ($this->check()){
            $eol = $this->eol;

            $currency   = $this->buildCurrency();
            $categories = $this->buildCategories();
            $deliveries = $this->buildDeliveryOptions();
            $offers     = $this->buildOffers();

            $content  = '';
            $content .= '<?xml version="'.$this->xml_version.'" encoding="'.$this->encoding.'"?>'.$eol;
            $content .= '<yml_catalog date="'.$this->date.'">'.$eol;
            $content .= '<shop>'.$eol;
                $content .= '<name>'.$this->name.'</name>'.$eol;
                $content .= '<company>'.$this->company.'</company>'.$eol;
                $content .= '<url>'.$this->main_url.'</url>'.$eol;
                $content .= $this->email ? '<email>'.$this->email.'</email>'.$eol: '';
                $content .= '<currencies>'.$eol.$currency.'</currencies>'.$eol;
                $content .= '<categories>'.$eol.$categories.'</categories>'.$eol;
                if ($deliveries) $content .= '<delivery-options>'.$eol.$deliveries.'</delivery-options>'.$eol;
                $content .= '<offers>'.$eol.$offers.'</offers>'.$eol;
            $content .= '</shop>'.$eol;
            $content .= '</yml_catalog>';

            $this->yml = $content;
        }else{
            return 0;
        }
    }
}