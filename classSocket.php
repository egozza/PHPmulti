<?php
/**
  * @author Зайцев Е.В.
  * @author Зайцев Егор Викторович  <egorza@mail.ru>
  */

/**
* Класс сокет сервер
* @package files
* @subpackage classes
*/
class socketServer {
    /**
     * $socket
     * @var resourse содержит дескриптор на сокет 
     **/
    protected $socket=null;

     /**
     * $ipAdress
     * @var string ip адрес
     */
    protected $ipAdress;
     /**
     * $port
     * @var integer порт для подключения сокета
     */
    protected $port;
     /**
     * $errno
     * @var integer номер ошибки при создании сокета
     * $errstr
     * @var string описание ошибки при создании сокета
     */

    protected $errno,$errstr;

     /**
     * $accept
     * @var resourse прослушиваемый сокет
     */
    protected $accept=null;
     /**
     * $Massage
     * @var class класс для обработки сообщений от клиета
     */
    protected $Massage;

    
     /**
     * контсруктор класса 
     * @param string $operator оператор для обработки сообщений от клиента
     * @param string $ipAdress ip адрес создаваемого сокета
     * @param integer $port номер порта
     */

    function __construct($operator,$ipAdress='127.0.0.1',$port=8000){
        $this->ipAdress=$ipAdress;
        $this->port=$port;
        $this->Massage=new massage($operator);
      
    }
    /**
     * Содание сокета
     * @return bool
     */
    public function createSocket(){

           if(($this->socket=stream_socket_server("tcp://".$this->ipAdress.":".$this->port,$this->errno,$this->errstr))===false)
            throw new Exception("Error №:$this->errno '$this->errstr' ");
        else
            return true;    
    }
   /**
     * принимаем сооединение с сокетом
     *  @return resourse
     */
    public function socketAccept(){
        if(($this->accept=stream_socket_accept($this->socket,-1))===false)
            throw new Exception("Error №:$this->errno '$this->errstr' ");
        else
            return $this->accept;
            
    }
   
    /**
     * Получить сокет
     * @return resourse
     */
    public function getSocket(){
        return $this->socket;
    }
    
     /**
     * Запись данных в сокет
     * @param $connect resourse сокет в который будем писать
     * @param $data string данные для записи
     */
    public function write($connect,$data){

           fwrite($connect,json_encode($data)."|");
    }
     /**
     *Закрываем сокет
     * @param $connect resourse сокет который будем закрывать
     */
    public function connectClose($connect){

            fclose($connect);
    }
     /**
     * Закрываем сокет сервера
     */
    public function CloseServer(){
            fclose($this->socket);
    }
     /**
     * Читаем строку из  сокета
     * @param $connect resourse сокет из которого будем читать
     * @return bool 
     * @return string
     */
    private function socketRead($connect){
        /**
        * $data
        * @var string полученный данные из сокета
        */
        $data="";
        $data.=stream_get_line($connect,2048,"|");
        if($data=="")
            return false;
        else

        return json_decode($data);
    }
     /**
     * вытаскиваем сообщение из сокета, пока не дойдем до конца
     * @param resourse сокет из которго будем читать
     * @return bool
     * @return string
     */
    public function read($connect){
        /**
        * $massMassage
        * @var array массив сообщений
        */
        $massMassage=array();
        while($data=$this->socketRead($connect)){
            
            $massMassage[]=$data;

        }
       
      
        if(sizeof($massMassage)>0)
        {   
            $result=$this->Massage->readMassageServer($massMassage,0);
            return $result;
        }
        else
            return false;
    }

     /**
     * Включаем на сокете не блокируемый режим
     * @param $connect resourse сокет который будем переводить в не блокирующий режим
     */
    public function streamNoBlock($connect){
        stream_set_blocking($connect,0);
    }
     /**
     * Включаем на сокете блокируемый режим
     * @return resourse
     */
    public function streamYesBlock($connect){
        stream_set_blocking($connect,1);
    }
   
} 
/**
* Класс сокет клиент
* @package files
* @subpackage classes
*/
class socketClient extends socketServer{
     /**
     * $massMassage
     * @var array массив сообщений
     **/
    private $massMassage=array();


    /**
     * контсруктор класса 
     * @param string $ipAdress ip адрес создаваемого сокета
     * @param integer $port номер порта
     */
	function __construct($ipAdress='127.0.0.1',$port=8000 ){
		$this->ipAdress=$ipAdress;
        $this->port=$port;

	}
	/**
     * Создаем сокет соединения , клиентское
     * @return bool
     */
    public function createSocket(){

           if(($this->socket=stream_socket_client("tcp://".$this->ipAdress.":".$this->port,$this->errno,$this->errstr,10,STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT))===false)
            throw new Exception("Error: Create Socket_Client");
        else
            return true;              
    }
    /**
     * Пишем строку в сокет
     */
     public function write($data){
           fwrite($this->socket,json_encode($data)."|");
    }
    /**
     * Удаляем сокет
     */
    public function stop(){
            fclose($this->socket);
            exit();
    }
    /**
     * читаем из сокета пока не встретим |
     * @return bool
     * @return string
     */
    private function socketRead(){
        /**
        * $data
        * @var string данные полученные из сокета
        */
        $data="";      
        if($data=stream_get_line($this->socket,2048,"|")=="")
        return false;
        else
        return json_decode($data);
          
    }
    /**
     * Читаем из сокета все сообщения
     * @return array
     */
    public function read(){
        $this->massMassage=array();
        while ($data=$this->socketRead()) {
            $this->massMassage[]=$data;
                
        }
       
       
        if(sizeof($this->massMassage)>0)
           return  $this->Massage->readMassageClient( $this->massMassage,$this->socket);



    }
    /**
     * Включаем на сокете не блокируемый режим
     */
    public function streamNoBlock(){
        stream_set_blocking($this->socket,0);
    }
    /**
     * Включаем на сокете блокируемый режим
     */
    public function streamYesBlock(){
        stream_set_blocking($this->socket,1);
    }
    
	
}

/**
* Класс для запуска скприптов
* @package files
* @subpackage classes
*/
class execScript{
     /**
     * $massProcess
     * @var array массив дескрипторов запущенных методом proc_open()
     **/
    private $massProcess=array();
     /**
     * $descriptorspec
     * @var array массив каналов для чтения и записи в дескриптор 
     **/
    private $descriptorspec = array(
             0 => array("pipe", "r"),
             1 => array("pipe", "w")
            );
     /**
     * $pipes
     * @var  ip адрес
     **/
    private $pipes;

    /**
     * Обрабатываем входные парамметры и запускаем скрипты
     * @param $nameScript string имя запускаемого скрита
     * @param $numProc integer число запускаемых скриптов
     * @param $params array массив параметров передоваемых скрипту
     */
    public function startScripts($nameScript,$numProc,$params,$metod){
    /**
     * $intParams
     * @var array массив переменых с инкрементом
     */
    $intParams=array();
     /**
     * $RandParams
     * @var array массив переменых случайных
     */
    $randParams=array();
        for($i=0;$i<$numProc;$i++)
        {
            foreach ($params["paramsInterator"] as $key => $value) {
                $intParams[$key]=$value+$i;
                
            }
            foreach ($params["randParams"] as $key => $value) {
                $randParams[$key]=rand(100000,10000000);
            }
           
                
            
       $this->massProcess[$i]=$this->startScript($nameScript,array(
        "intParams"=>$intParams,
        "params"=>$params["params"],
        "randParams"=>$randParams),$metod);

   }


    }   
         /**
     * Запускаем скрипт с заданными парамметрами
     * @param $nameScript string имя запускаемого скрита
     * @param $params array массив параметров передоваемых скрипту
     */
        private function startScript($nameScript,$params=array(),$metod)
    
        {    
        /**
        * $oneScriptParams
        * @var array массив переменых
        */
        $oneScriptParams=array();
        foreach ($params["intParams"] as $key => $value) {
            $oneScriptParams[$key]=$value;
        }
        foreach ($params["params"] as $key => $value) {
            $oneScriptParams[$key]=$value;
        }
        foreach ($params["randParams"] as $key => $value) {
            $oneScriptParams[$key]=$value;
        }
       if(!empty($metod))
       {
        $string=file_get_contents($nameScript);
        $string=explode("?>", $string);
        $s=$string[0]."\n".$metod."\n"."?>";
       
        $process = proc_open("php", $this->descriptorspec, $pipes,null,$oneScriptParams);
        if (is_resource($process)) {
        fwrite($pipes[0], $s);
        fclose($pipes[0]);
        fclose($pipes[1]);
        return $process;
       }}
    $process = proc_open("php", $this->descriptorspec, $pipes,null,$oneScriptParams);
    if (is_resource($process)) {
    fwrite($pipes[0], file_get_contents($nameScript));
    fclose($pipes[0]);
    fclose($pipes[1]);
    return $process;
    }
    }   

     /**
     * Получаем данные о всех запущенных скриптах
     */
    public function getStatus(){
        foreach ($this->massProcess as $key => $value) {
           
        /**
        * $arrayStatus
        * @var integer статус скрипта
        */
       $arrayStatus=proc_get_status($value);
      if($arrayStatus['running'])
        $status=" work ";
    else
        $status=" stop ";
       echo "   process pid=".$arrayStatus['pid']." status=".$status."\n"; 
   }

    }

     /**
     * Закрывем все запущенные скрипты
     */
    public function closeScripts(){

   
   
        foreach ($this->massProcess as $key => $value) {
        
            proc_close($value);
        }

    }
    }
/**
* Класс математических функций
* @package files
* @subpackage classes
*/
class mathOperator{
         /**
     * Функция сложения
     * @param $arg1 integet первый аргумент
     * @param $arg2 integer второй аргумент
     */
        public function plus($arg1,$arg2){
            return $arg1+$arg2;
        }

        /** Функция вычитания
     * @param $arg1 integet первый аргумент
     * @param $arg2 integer второй аргумент
     */
        public function minus($arg1,$arg2){
            return $arg1-$arg2;
        }

    }

/**
* Класс сообщений
* @package files
* @subpackage classes
*/
    class massage{
        /**
         * $operator
         * @var string математический или другой оператор для обработки сообщений от клиента
         **/
        
        private $operator;
         /**
         * $mathOperator
         * @var object mathOperator объект класса mathOperator
         **/
        private $mathOperator;
         /**
         * $resulData
         * @var  возвращаемые данные
         **/
        private $resulData;
       
        /**
     * конструктор класса 
     * @param $operator string оператор для обработки сообщений 
     */
        function __construct($operator){
            $this->mathOperator=new mathOperator();

            switch($operator)
            {
                case "-":
                 $this->operator="-";
                break;
                case "+":
                $this->operator="+";
                break;
                case "end":
                $this->operator="end";
                break; 
                default:
                throw new Exception("Неверный оператор");
                break;
            }
           
                    

        }

         /**
         * Функция для чтения массива сообщений от клиента
         * @param $massMassage array массив сообщений 
         * @param $bufer integer буферная переменая для хранения промежуточного результата
         * @return array
          **/
        function readMassageServer($massMassage=array(),$bufer)
        {   
            
            for($i=0;$i<sizeof($massMassage);$i++)
            foreach ($massMassage[$i] as $key => $value) {
                   
                       switch ($key) {
                        case "result":
                                switch($this->operator)
                                {
                                    case "+":
                                    $this->resulData=$this->mathOperator->plus($bufer,$value);
                                    $bufer=$this->resulData;
                                    break;
                                    case "-":
                                    $this->resulData=$this->mathOperator->minus($bufer,$value);
                                        $bufer=$this->resulData;
                                    break;
                                    case "end":
                                    if(is_array($value))
                                        {  $this->resulData=array();
                                        foreach ($value as $k => $val) {

                                           $this->resulData[]=$val;
                                        }
                                    }
                                    else
                                        $this->resulData=$value;

                                    break;
                                }


                            break;
                    
                    case "comand":

                                switch ($value) {

                                    case 'exit':
                               
                                  return   array("result"=>$this->resulData,"comand"=>"exit"); 
                                    
                                    
                                }

                    break;

                }
                
            }
               
            return array("result"=>$this->resulData,"comand"=>"");
        }
         /**
         * Функция для чтения массива сообщений от сервера
         * @param $massMassage array массив сообщений 
         * @param $client socketClient объект soketClient 
         * @return array
          **/
        function readMassageClient($massMassage=array(),socketClient $client){
             for($i=0;$i<sizeof($massMassage);$i++)
            foreach ($massMassage[$i] as $key => $value) {
                    if($key=="comand")
                    {
                        switch($value){
                            case "exit":
                            $client->stop();
                            break;
                            case "start":
                                 $client->streamNoBlock();
                                

                            break;

                        }
                    }


                            
                    }
                
            
            return array("result"=>$this->resulData);

        }
    }
    
    /**
    * Класс время
    * @package files
    * @subpackage classes
    */
    class time{
         /**
         * $time
         * @var integer время последнего запроса
         **/
        private $time;
         /**
         * $limit
         * @var integer размер времени при прохождении которого проиходит разрешение на выполнение запроса
         **/
        private $limit;
        /**
         * $timeStart
         * @var integer время старта задачи
         **/
        private $timeStart;
         /**
         * $maxLimit
         * @var integer разбег для рандомного создания лимита
         **/
        private $maxLimit;
        /**
     * контсруктор класса 
     * @param $maxLimit integet  разбег для рандомного создания лимита
     */
        function __construct($maxLimit)
        {
            if(empty($maxLimit))
            throw new Exception("Не задан временной лимит");
        else{
            

            mt_srand(time());
            $this->maxLimit=$maxLimit;
        }
        }
        /**
         * Установка времнени срабатывания оперциии
         * @param $time integer время
         */

        public function setTime($time){
            $this->time=$time;
        }
         /**
         * Установка времнени срабатывания оперциии
         * @param $time integer установка размера времени при прохождении которого проиходит разрешение на выполнение запроса
         */

        public function setLimit($limit){
            $this->limit=$limit;
        }
         /**
         * Установка времения старта
         */
        public function setStartTime(){
            if(empty($this->timeStart))
            $this->timeStart=time();
        }
         /**
         * Генерация случайного промежутка времени
         * @return integer 
         */
        public function randTime(){
            return (mt_rand(0,$this->maxLimit*1000)/1000);
        }
         /**
         * Получение текущего времени
         * @return integer 
         */
        public function Time(){
            return time();
        }
         /**
         * Получение  времени текущей операции
         * @return integer время в секндах
         */
        public function getTime(){
            return $this->time;
        }
         /**
         * Получение лимита
         * @return integer 
         */
        public function getLimit(){
            return $this->limit;
        }

        /**
         * Получение времени старта
         * @return integer 
         */
        public function getStartTime(){
            return $this->timeStart;
        }
        
        /**
         * Функция определяющая дать разрешение на оперцию или нет
         * @return bool
         */
        public function startTask(){
            if(empty($this->limit)&&!empty($this->timeStart))
            {   
                $this->setLimit($this->randTime());
                $this->setTime(time());
                return true;

            }
            elseif($this->timePassed()>=$this->getLimit()){
                    $this->setLimit($this->randTime());
                    $this->setTime(time());
                    return true;
            }
            else
            {
                    return false;
            }
        }
        /**
         * Функция определяюща прошедшее время с предыдущей операции
         * @return integer
         */
    public function timePassed(){
        return ($this->Time()-$this->getTime());

    }
    /**
         * Функция определяюща прошедшее время со старта задачи
         * @return integer
         */
    public function timePassedStart(){
        return ($this->Time()-$this->getStartTime());
    }
    }

?>