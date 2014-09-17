<?php
/**
* Подключаем файл
*/
include 'classSocket.php';
/**
* Класс вычисления пи
* @package files
* @subpackage classes
*/
class pi{
    /**
     * $nCircle
     * @var integer число точек попавших в круг
     **/
    private $nCircle=0;
    /**
     * $maxPoint
     * @var integer максимальное число точек
     **/
    private $maxPoint;
    /**
     * $point
     * @var integer текущее число точек
     **/
    private $point;
    /**
     * $rBox
     * @var integer сторона квадрата
     **/
    private $rBox;
    /**
     * $y
     * @var integer координаты по оси y
     * $x
     * @var integer координаты по оси x
     **/
    private $y,$x;
    /**
     * $socket
     * @var resourse сокет
     **/
    private $socket;
    /**
     * $time
     * @var object time объект класса time
     **/
    private $time;

 /**
     * контсруктор класса 
     * @param $ip string ip адрес
     * @param $port integer порт
     */
    function __construct($ip,$port){
        mt_srand(time());
        $this->rBox=$_ENV['r'];
        $this->maxPoint=$_ENV['n'];

        if($ip==null){
            if($port==null){
                $this->socket=new socketClient();
            }
        }
        elseif($port==null)
        {
           $this->socket=new socketClient($ip,$port); 
        }
        else
            $this->socket=new socketClient();

        $this->time=new time(2);
        $this->socket->createSocket();




    }
     /**
     * функия получения случайной координаты
     */
   private function random(){
        return (mt_rand(0,$this->rBox*1000000)/1000000);

    }
     /**
     * Функция установки значений X и Y
     */
    private function setXY(){
        $this->y=$this->random();
        $this->x=$this->random();
       
    }
     /**
     * Функция получения Y
     * @return integer
     */
    private function getY(){
        if(empty($this->y))
            throw new Exception("У не задан, ошибка");
        else 
            return $this->y; 
            
    }
     /**
     * Функция получения X
     * @return integer
     */
    private function getX(){
        if(empty($this->x))
            throw new Exception("X не задан, ошибка");
        else 
            return $this->x; 
            
    }
     /**
     * Функция получения окружностисти с координатами X и Y
     * @return integer
     */
    private function circle(){
        return (pow($this->x,2)+pow($this->y,2));
    }
     /**
     * Функция тарта вычисления пи
     * @return integer
     */
    public function startPi(){
        $this->socket->read();

        for($this->point=0;$this->point<$this->maxPoint;$this->point++){
            $this->setXY();
            if(pow($this->rBox,2)>$this->circle())
                $this->nCircle++;
            if($this->time->startTask())
                $this->writePi();
            
            elseif(($this->point+1)>=$this->maxPoint)
               { $this->writePi();
                $this->socket->write(array("comand"=>"exit"));
                $this->socket->stop();
               }
               
            

        }
         
            }

    /**
     * Функция записи в сокет данных о ПИ
     **/
    private function writePi(){
        $this->socket->write(array("result"=>array("nCircle"=>$this->nCircle,"nPoint"=>$this->point)));
        
    }

}

?>