<?php
/**
 * @name YMLBuilder 2.1
 * @description Клас для создания yml-файла выгрузки для Яндекса
 * @class_uri:  Ссылка на инфо о плагине
 * @author_uri:  https://t.me/ivan_peshko/
 * @author Ivan Peshko
 * @version 2.1
 *
 */
class YMLBuilder {

    private $error = false;
    private $error_massive;
    private $eol = "\r\n";
    private $header = "";

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
     * @param string $encoding File encoding
     */
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }
    /**
     * @param string $name Site name
     */
    public function setName($name) {
        $this->name = $name;
    }
    /**
     * @param string $company Company name
     */
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

    /**
     * cTag - create html tag with data and attributes
     * @param string $tagName Tag name
     * @param array $attributes array of attributes
     * @param string $data Data to put inside of tag
     * @return string
     * */
    private function cTag($tagName, $attributes = array(), $data = ''){
        $eol = $this->eol;
        $tag  = '';
        $attribute = '';
        if ($attributes)
        foreach ($attributes as $name => $value)
            if ($value || $value == 0) $attribute .= ' '.$name.'="'.$value.'"';

        if ($data) $tag .= '<'.$tagName.$attribute.'>'.$data.'</'.$tagName.'>'.$eol;
        else $tag .= '<'.$tagName.$attribute.'/>'.$eol;

        return $tag;
    }

    private function buildCurrency(){
        $currencies = $this->currencies;
        $string ='';
        foreach ($currencies as $currency)
            $string .= $this->cTag('currency',array('id'=>$currency['id'],'rate'=> $currency['rate']));

        return $string;
    }
    private function buildCategories(){
        $categories = $this->categories;
        $string ='';
        foreach ($categories as $category){
            $string .= $this->cTag('category',array(
                'id'=>$category['id'],
                'parentId'=> $category['parent_id']
                ),
                $category['name']
            );
        }
        return $string;
    }

    private function buildDeliveryOptions(){
        $deliveries = $this->deliveries;
        $del ='';
        if ($deliveries != '') {
            foreach ($deliveries as $delivery){
                $del .= $this->cTag('option',array(
                    'cost'=>$delivery['cost'],
                    'days'=> $delivery['days'],
                    'order-before' => $delivery['order-before'])
                );
            }
        }
        return $del;
    }

    private function buildOffers(){
        $offers = $this->offers;
        $string ='';
        foreach ($offers as $offer){
            $data ='';
            $data .= $this->cTag('name','',$offer['name']);
            if ($offer['typePrefix']) $data .= $this->cTag('typePrefix','',$offer['typePrefix']);
            if ($offer['vendor']) $data .= $this->cTag('vendor','',$offer['vendor']);
            $data .= $this->cTag('url','',$offer['url']);
            $data .= $this->cTag('price','',$offer['price']);
            $data .= $this->cTag('currencyId','',$offer['currencyId']);
            $data .= $this->cTag('categoryId','',$offer['categoryId']);
            foreach ($offer['picture'] as $picture_url) if ($picture_url!='') $data .= $this->cTag('picture','',$picture_url);
            !$offer['store'] ? $data .= $this->cTag('store','','false') : $data .= $this->cTag('store','','true');
            !$offer['pickup'] ? $data .= $this->cTag('pickup','','false') : $data .= $this->cTag('pickup','','true');//$string .= '<pickup>false</pickup>'.$eol : $string .= '<pickup>true</pickup>'.$eol;
            !$offer['delivery'] ? $data .= $this->cTag('delivery','','false') : $data .= $this->cTag('delivery','','true');//$string .= '<delivery>false</delivery>'.$eol : $string .= '<delivery>true</delivery>'.$eol;
            if ($offer['delivery'] && $offer['delivery-options'])
                foreach ($offer['delivery-options'] as $options)
                    $data .= $this->cTag('option',array(
                        'cost' => $options['cost'],
                        'days' => $options['days'],
                        'order-before' => $options['order-before']
                    ));
            if ($offer['description']) $data .= $this->cTag('description','',$offer['description']);
            if ($offer['param'])
                foreach ($offer['param'] as $param)
                    $data .= $this ->cTag('param',
                        array(
                            'name' => $param['name'],
                            'unit' => $param['unit']
                        ),
                        $param['value']);
            if ($offer['sales_notes']) $this->cTag('sales_notes','',$offer['sales_notes']);
            if ($offer['manufacturer_warranty']) $this->cTag('manufacturer_warranty','',$offer['manufacturer_warranty']);
            if ($offer['country_of_origin']) $this->cTag('country_of_origin','',$offer['country_of_origin']);
            if ($offer['barcode']!='') $this->cTag('barcode','',$offer['barcode']);

            $string .= $this ->cTag('offer', array(
                'id' => $offer['id'],
                'bid' => $offer['bid']
            ),$data);
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