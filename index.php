<?php
    //Импортируем класс
    require_once "functions.php";

    //Создаем объект на основе класса
    $request = new Requests();
    
    //Получаем список отгрузок
    $demands = $request->get_demands();

    //Проверяем код ошибки
    if ($demands[0]!=200) {
        exit("Ошибка при получении списка Отгрузок");
    }
    
    //Получаем список платежей
    $paymentin = $request->get_payments();

    //Проверяем код ошибки
    if ($paymentin[0]!=200) {
        exit("Ошибка при получении списка Платежей");
    }

    //Выбираем только те платежи, которые еще не привязаны к документу
    foreach (json_decode($paymentin[1], true)["rows"] as $value) {
        if (!array_key_exists("operations", $value)) {
            $payments_filtered[] = array("id" => $value["id"], "sum" => $value["sum"]);

        }
    }

    //ID контрагента по которому будем делать фильтр
    $contragent_id ="daa06aa5-d1eb-11ec-0a80-0e28000f1643"; //ООО "Поставщик"

    $tho_months_ago = time() - (60 * 24 * 60 * 60);
    
    //Перебираем отгрузки в цикле
    foreach (json_decode($demands[1], true)["rows"] as $demand_val) {
        $cre_date = strtotime($demand_val["created"]);

        //Выбираем отгрузки по заданному контрагенту, которые были созданы за посление 2 месяца и к которым не привязаны платежи
        if ($demand_val["agent"]["meta"]["href"]=="https://online.moysklad.ru/api/remap/1.2/entity/counterparty/".$contragent_id  && $cre_date >= $tho_months_ago
            && !array_key_exists('payments', $demand_val)
            ) 
        {
            //Перебираем не привязанные платежи
            foreach($payments_filtered as $payment_key => $payment_val) {

                //Если сумма платежа меньше или равно неоплаченной суммы отгрузки, то привязываем платеж
                if($payment_val["sum"]<=$demand_val["sum"]-$demand_val["payedSum"]) { 

                    //Привязываем платеж к Отгрузке
                    $result = $request->link_pay_to_demaind($payment_val["id"], $demand_val["meta"]);

                    //Проверяем код ошибки
                    if ($result[0]!=200) {
                        exit("Ошибка при привязке платежа к Отгрузке");
                    }

                    //Удаляем призязанный платеж из нашего массива
                    unset($payments_filtered[$payment_key]);

                    //Выходим из цикла
                    break;

                }
            }
        }
    };



?>
