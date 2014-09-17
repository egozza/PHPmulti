<?php

/**
* Подключаем файл
*/
include 'classSocket.php';

set_time_limit(0);
ob_implicit_flush();


/**
* Класс вычисления пи
* @package files
* @subpackage classes
*/
class piTask{
	/**
     * $pi
     * @var integer содержит чило пи
     **/
	private $pi;
	/**
     * $nPoint
     * @var integer содержит общее число точек
     **/
	private $nPoint;
	/**
     * $socket
     * @var integer содержит общее чило точек попавших в окружность
     **/
	private $nCircl;


   /**
     * контсруктор класса 
     */
	function __construct(){
		$this->pi=0;
		$this->nPoint=0;
		$this->nCircl=0;
	}
	 /**
     *Задание nCircl и nPoint
     */
	public function setPi($nCircl,$nPoint)
	{
		$this->nCircl=$nCircl;
		$this->nPoint=$nPoint;
	}
	 /**
     * вычисление PI
     */
	private function pi(){
		if($this->nCircl!=0)
		$this->pi=($this->nCircl/$this->nPoint)*4;
	}
	 /**
     * Печать PI на экран
     */
	public function printPi($timePassed){
		$this->pi();
		echo " PI= '$this->pi ,real pi='".pi()."' time passed=$timePassed \n";


	}
}

/**
* Класс сервер
* @package files
* @subpackage classes
*/
class server{
	/**
     * $connects
     * @var array массив всех сокетов
     **/
	private $connects=array();
	/**
     * $read
     * @var array массив всех доступных сркетов для чтения
     **/
	private $read=array();
	/**
     * $socket
     * @var resourse дескриптор сокета
     **/
	private $socket;
	
	/**
     * $massProc
     * @var array массив процессов
     **/
	private $massProc;
	/**
     * $numTask
     * @var integer число запускаемых процессов
     **/
	private $numTask;
	/**
     * $time
     * @var object time объект класса time
     **/
	private $time;
	/**
     * $nameScript
     * @var string имя скрипта
     **/
	private $nameScript;
	/**
     * $data
     * @var array полученные данные от клиента
     **/
	private $data;
	/**
     * $pi
     * @var object pi объект класса pi
     **/
	private $pi;

	/**
     * контсруктор класса 
     */
	function __construct(){
		$this->socket=new socketServer("end");
		$this->massProc=new execScript();
		$this->time=new time(2);
		$conf=parse_ini_file("config.ini");
		$this->numTask=$conf["nProch"];
		$this->nameScript=$conf["nameScript"];
		$this->data='';
		$this->pi=new piTask();



	}
	/**
     * Запуск сервера
     * @param $arrayParams array массив парамметров  для запуска скриптов
     */
	public function startServer($arrayParams,$metod){

		if($this->createSoctet())
		{
			echo "CreateSoketServer\n\n\n";
			$this->socket->streamNoBlock($this->socket->getSocket());
			$this->startScript($arrayParams,$metod);
			while(true){
				$this->read=$this->connects;
				$this->read[]=$this->socket->getSocket();
				
				

				//$this->socket->streamSelect($this->read);
				stream_select($this->read, $write=null, $except=null, 0);
				
			if(in_array($this->socket->getSocket(),$this->read))
			{	
			echo "New connect\n";

			$new=$this->socket->socketAccept();
			$this->socket->streamNoBlock($new);
			$this->socket->write($new,array("comand"=>"start"));
			$this->time->setStartTime();
			
			$this->connects[]=$new;
			unset($this->read[ array_search($this->socket->getSocket(), $this->read)]);

			}
			if($this->time->startTask()){

				foreach ($this->connects as $key => $connect) {
					
					if($this->data=$this->socket->read($connect))
					{	
						foreach ($this->data['result'] as $k=> $value) {
							$mass[$k]=$value;
						}
						$this->pi->setPi($mass["nCircle"],$mass["nPoint"]);
						
						if($this->data['comand']=="exit")
						{
						unset($this->connects[array_search($connect, $this->connects)]);
						$this->numTask--;
						if($this->numTask==0)
							break 2;
						}
					}
				}
				$this->pi->printPi($this->time->timePassedStart());
				$this->massProc->getStatus();

			}

			}
			echo " The End \n\n";
			$this->socket->CloseServer();
			$this->massProc->closeScripts();



		}
		

	}
	/**
     * Создание сокета
     * @return resourse
     */
	private function createSoctet(){
		return $this->socket->createSocket();

	}
	/**
     * Запуск скриптов
     * @param $arrayParams массив парамметров для запуска скриптов 
     */
	private function startScript($arrayParams,$metod){
		$this->massProc->startScripts($this->nameScript,$this->numTask,$arrayParams,$metod);
		echo "Script Start\n\n";
	}

}
$server=new server();
$server->startServer(array("paramsInterator"=>array(),
	"params"=>array("r"=>"1000"),"randParams"=>array("n"=>"")),"\$pi=new pi(null,null); \$pi->startPi();");


?>