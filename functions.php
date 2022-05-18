<?php
    class Requests {
        private $login = "admin@bahrom-ermatov";
        private $password = "observer";
        private $link;
        private $header;
        private $method;
        private $data;

        //Функция для отправки запроса в сторону приложения Мой Склад
        private function send_request($link, $header, $data, $method)
        {
            $curl = curl_init();
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl,CURLOPT_USERAGENT,'');
            curl_setopt($curl,CURLOPT_URL, $link);
            curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl,CURLOPT_HEADER, false);
            curl_setopt($curl,CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
            $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            return array( $code, $out );
        }

        //Функция для получения списка Отгрузок
        public function get_demands()
        {
            $this->link = "https://online.moysklad.ru/api/remap/1.2/entity/demand"; //URL для запроса
            $this->header=["Authorization:Basic ".base64_encode($this->login.":".$this->password)]; //Определяем значение хидера
            $this->method = "GET"; //Определяем метод запроса
            $this->data = "";
            
            //Отправляем запрос
            $result = $this->send_request($this->link, $this->header, $this->data, $this->method);
    
            return $result;
        }

        //Функция для получения списка Платежей
        public function get_payments()
        {
            $this->link = "https://online.moysklad.ru/api/remap/1.2/entity/paymentin"; //URL для запроса
            $this->header=["Authorization:Basic ".base64_encode($this->login.":".$this->password)]; //Определяем значение хидера
            $this->method = "GET"; //Определяем метод запроса
            $this->data = "";
            
            //Отправляем запрос
            $result = $this->send_request($this->link, $this->header, $this->data, $this->method);
    
            return $result;
        }

        //Функция для привязки платежа к отгрузке
        public function link_pay_to_demaind($payment_id, $demand_meta)
        {
            $this->link = "https://online.moysklad.ru/api/remap/1.2/entity/paymentin/".$payment_id; //URL для запроса
            $this->header=["Authorization:Basic ".base64_encode($this->login.":".$this->password), 'Content-Type:application/json']; //Определяем значение хидера
            $this->method = "PUT"; //Определяем метод запроса
            $this->data = array(
                                "operations" =>[
                                    array(
                                        "meta" => $demand_meta
                                    )
                                ]
                            );
            
            //Отправляем запрос
            $result = $this->send_request($this->link, $this->header, $this->data, $this->method);
    
            return $result;
        }
    }


?>
