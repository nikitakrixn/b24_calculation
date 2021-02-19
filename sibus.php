<?

//Инициализируем библиотеку
require_once (__DIR__.'/crest.php');

//Переменные
$ID_deal = $_REQUEST["properties"]["Id_deal"]; //1977
$ID_materiali = $_REQUEST["properties"]["Id_materials"]; //147
$ID_raboti = $_REQUEST["properties"]["Id_works"]; //149
$ID_templete = $_REQUEST["properties"]["Id_template"]; //43

$deal_product = CRest::call('crm.deal.productrows.get', array("ID" => $ID_deal));
$deal_get = CRest::call('crm.deal.get',  array("ID" => $ID_deal));
$company_get = CRest::call('crm.company.get', array("ID" => $deal_get["result"]["COMPANY_ID"]));
$raboti_list = [];
$materiali_list = [];

$sum_raboti = 0;
$sum_materiali = 0;



//Заполнение массива товарами из каталога работы
foreach($deal_product["result"] as &$value)
{
    $product = CRest::call('crm.product.get', array("ID" => $value["PRODUCT_ID"]));

    $section_raboti = sectioncheck($ID_raboti);

    foreach($section_raboti as $key){
        if ($product["result"]["SECTION_ID"] == $key["Sid"])
        {
            $raboti_list[] = [
                "ID" => $value["ID"],
                "Name" => $value["PRODUCT_NAME"],
                "Price" => $value["PRICE"],
                "Quantity" => $value["QUANTITY"],
                "Sum" => $value["PRICE"]*$value["QUANTITY"]
            ];
            $sum_raboti = $sum_raboti + $value["PRICE"]*$value["QUANTITY"];
        }  
    }
}

//Заполнение массива товарами из каталога материалы
foreach($deal_product["result"] as &$value )
{
    $product = CRest::call('crm.product.get', array("ID" => $value["PRODUCT_ID"]));

    $section_materiali = sectioncheck($ID_materiali);

    foreach($section_materiali as $key){
        if ($product["result"]["SECTION_ID"] == $key["Sid"]) 
        {
            $materiali_list[] = [
                "ID" => $value["ID"],
                "Name" => $value["PRODUCT_NAME"],
                "Price" => $value["PRICE"],
                "Quantity" => $value["QUANTITY"],
                "Sum" => $value["PRICE"]*$value["QUANTITY"]
            ];
            $sum_materiali = $sum_materiali + $value["PRICE"]*$value["QUANTITY"];
        } 
    }
}

//Итоговая сумма
$total_sum = $sum_materiali+$sum_raboti;

//Сделка 
$deal_calculation = array(
    "RabotaSum" => $sum_raboti,
    "MaterialiSum" => $sum_materiali,
    "TotalSum" => $total_sum,
    "CompanyTitle" => $company_get["result"]["TITLE"],
    "CompanyPhone" => $company_get["result"]["PHONE"][0]["VALUE"],
    "CompanyEmaile" => $company_get["result"]["EMAIL"][0]["VALUE"] 
);

//Создание документа
$documentgenerator_update = CRest::call('crm.documentgenerator.document.add', array(
    "templateId" => $ID_templete, 
    "entityTypeId" => 2, 
    "entityId" => $ID_deal, 
    "values" => [
        'CMT' => $deal_calculation["CompanyTitle"],
        'CMP' => $deal_calculation["CompanyPhone"],
        'CME' => $deal_calculation["CompanyEmaile"],
        'TableRaboti' => $raboti_list,
        'Rid' => 'TableRaboti.Item.ID',
        'Rname' => 'TableRaboti.Item.Name',
        'Rcen' => 'TableRaboti.Item.Price',
        'Rkol' => 'TableRaboti.Item.Quantity',
        'Ritog' => 'TableRaboti.Item.Sum',
        'Rsum' => $deal_calculation["RabotaSum"],
        'TableMateriali' => $materiali_list,
        'Mid' => 'TableMateriali.Item.ID',
        'Mname' => 'TableMateriali.Item.Name',
        'Mcen' => 'TableMateriali.Item.Price',
        'Mkol' => 'TableMateriali.Item.Quantity',
        'Mitog' => 'TableMateriali.Item.Sum',
        'Msum' => $deal_calculation["MaterialiSum"],
        'TotalSum' => $deal_calculation["TotalSum"]
    ],
    'fields' => [
        'TableRaboti' => [
                'PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\ArrayDataProvider',
                'OPTIONS' => [
                    'ITEM_NAME' => 'Item',
                    'ITEM_PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\HashDataProvider',
                ],
            ],
        'TableMateriali' => [
            'PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\ArrayDataProvider',
            'OPTIONS' => [
                'ITEM_NAME' => 'Item',
                'ITEM_PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\HashDataProvider',
            ],
        ],
    ]
));


$id_doc = $documentgenerator_update["result"]["document"]["id"];

$id_fileonDisk = $documentgenerator_update["result"]["document"]["emailDiskFile"];

$download_file = CRest::call('disk.file.get', array("id"=> $id_fileonDisk));

$publ_curl =CRest::call('crm.documentgenerator.document.enablepublicurl', array("id" => $id_doc, "status" => 1));

 
$bizproc_send = CRest::call('bizproc.event.send', array(
    "auth" => $_REQUEST["auth"]["access_token"],
    "event_token" => $_REQUEST["event_token"],
    "log_message" => "OK!!@!#!@!@#!!!@@#!@#!!!",
    "return_values" => array(
        "downloadUrlMachine" => $download_file["result"]["FILE_ID"],
        "public_url" => $publ_curl["result"]["publicUrl"],
        
    )
));


function sectioncheck($data) {

    $start = CRest::call('crm.productsection.list', array('filter' => ["SECTION_ID" => $data]));
    
    if(empty($start["result"])){
        $section_list[] = [
            "Sid" => $data
        ];
    }
    else {
        foreach($start["result"] as $value) {
        
            $section_list[] = [
                "Sid" => $value["ID"]
            ];
    
        }
        $section_list[] = ["Sid" => $data];
    }

    return $section_list;
}



