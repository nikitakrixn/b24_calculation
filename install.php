<?php
require_once (__DIR__.'/crest.php');

$result = CRest::call(
	'bizproc.activity.add',
	[
		"CODE" => "df4444776cd2be0de7b26fc02a928654",
		"HANDLER" => "https://intercalation.ru/webhook/sibus/sibus.php",
		"USE_SUBSCRIPTION" => "Y",
		"NAME" => "Калькуляция",
		"DESCRIPTION" => "Описание калькуляции...",
		"PROPERTIES" => [
			"Id_deal" => [
				"Name" => "Индификатор сделки",
				"Type" => "int",
				"Required" => "Y",
				"Multiple" => "N"
			],
			"Id_materials" => [
				"Name" => "Индификатор материалов",
				"Type" => "int",
				"Required" => "Y",
				"Multiple" => "N",
				"Default" => "147"
			],
			"Id_works" => [
				"Name" => "Индификатор работ",
				"Type" => "int",
				"Required" => "Y",
				"Multiple" => "N",
				"Default" => "149"
			],
			"Id_template" => [
				"Name" => "Индификатор шаблона",
				"Type" => "int",
				"Required" => "Y",
				"Multiple" => "N",
				"Default" => "43"
			],
		],
		"RETURN_PROPERTIES" => [
			"downloadUrlMachine" => [
				"Name" => "Документ в docx",
				"Type" => "string",
				"Multiple" => "N",
				"Default" => null
			],
			"public_url" => [
				"Name" => "Публичная ссылка на документ",
				"Type" => "string",
				"Multiple" => "N",
				"Default" => null
			],
		],
	]
);

echo '<pre>';
print_r($result);
echo '</pre>';

$result = CRest::installApp();
if($result['rest_only'] === false):?>
<head>
	<script src="//api.bitrix24.com/api/v1/"></script>
	<?if($result['install'] == true):?>
	<script>
		BX24.init(function(){
			BX24.installFinish();
		});
	</script>
	<?endif;?>
</head>
<body>
	<?if($result['install'] == true):?>
		installation has been finished
	<?else:?>
		installation error
	<?endif;?>
</body>
<?endif;