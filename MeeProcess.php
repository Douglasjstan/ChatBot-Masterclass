<?php
date_default_timezone_set('Asia/Singapore');

function processMessage($update) {
    if ($update["queryResult"]["action"] == "ConfirmOrder"){
        $outputContexts = $update["queryResult"]["outputContexts"];
        foreach($outputContexts as $outputContext)
        {
            if(endsWith($outputContext["name"],'/session_vars'))
            {
                $Name = $outputContext["parameters"]["Name"];
                $PlateSize = $outputContext["parameters"]["PlateSize"];
                if ($PlateSize == "Large") {
                    $Cost = 10;
                } else if ($PlateSize == "Medium") {
                    $Cost = 6;
                } else {
                    $Cost = 4;
                }
                $DeliveryMethod = $outputContext["parameters"]["DeliveryMethod"];
                if($DeliveryMethod == "Self-collect")
                {
                    $Address = $DeliveryMethod;
                }
                else{
                    $Address = $outputContext["parameters"]["Address"];
                }
                $ChilliAmount = $outputContext["parameters"]["ChilliAmount"];
                $Quantity = $outputContext["parameters"]["Quantity"];
                $Phone = $outputContext["parameters"]["Phone"];

                $Total = strval((int)$Quantity * $Cost);

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://mydatabase-4b97.restdb.io/rest/orders",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{\"Name\":\"".$Name."\",\"Size\":\"".$PlateSize."\",\"Qty\":\"".$Quantity."\",\"Chilli\":\"".$ChilliAmount."\",\"Address\":\"".$Address."\",\"Total\":\"".$Total."\",\"Phone\":\"".$Phone."\"}",
                    CURLOPT_HTTPHEADER => array(
                        "Cache-Control: no-cache",
                        "Content-Type: application/json",
                        "Postman-Token: 2a94321b-210a-46f2-99fd-99757f620b43",
                        "x-apikey: 3c5433bdc355072daf214f300bef125d6f7f8"
                    ),
                ));

                $Response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    sendMessage(array(
                        "fulfillmentText" => 'Ready in 4 hours. Thank you!'
                    ));
                }
            }
        }
    }
    else {
        echo "Have a good night!";
    }
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function sendMessage($parameters) {
    echo json_encode($parameters);
}
//echo "xxx";
$update_response = file_get_contents("php://input");
echo $update_response;
$update = json_decode($update_response, true);
if (isset($update["queryResult"]["action"])) {
    processMessage($update);
}

