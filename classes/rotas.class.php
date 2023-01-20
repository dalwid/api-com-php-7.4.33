<?php

class Rotas 
{

    private $listRotes = [''];
    private $listCallBack = [''];
    private $listProtected = [''];
    
    public function add($method, $route, $callback, $protected){
        $this->listRotes[]     = strtoupper($method).':'.$route;
        $this->listCallBack[]  = $callback;
        $this->listProtected[] = $protected; 

        return $this;
    }
        
    public function ir($route){

        $callback  = '';
        $protected = '';
        $param     = '';

        $methodServer = $_SERVER['REQUEST_METHOD'];
        $methodServer = isset($_POST['_method']) ? $_POST['_method'] : $methodServer;        
        $route = $methodServer.":/".$route;

        
        if(substr_count($route, "/") >= 3){
            $param = substr($route, strrpos($route, "/") + 1);
            $route = substr($route, 0, strrpos($route, "/"))."/[PARAM]";
        }
        
        //var_dump($route);
        //print_r($this->listRotes);
        
        $indice = array_search($route, $this->listRotes);
        if($indice > 0){
            $callback = explode("::", $this->listCallBack[$indice]);
            $protected = $this->listProtected[$indice];
        }

        $class  = isset($callback[0]) ? $callback[0] : '';
        $method = isset($callback[1]) ? $callback[1] : '';

       
        if(class_exists($class)){
            
            if(method_exists($class, $method)){
               
                $instanceClass = new $class();
                if($protected){
                    $verefi = new Usuarios();
                    if($verefi->verificar()){
                        return call_user_func_array(
                            array($instanceClass, $method),
                            array($param)
                        );                        
                    }
                    else{
                        echo json_encode(["dados " => "token invalido."]);
                    }                   
                }
                else{                    
                    $instanceClass = new $class();
                    return call_user_func_array(
                        array($instanceClass, $method),
                        array($param)
                    );
                }
            }
            else{
                $this->notExists();
            }

        }
        else{
            $this->notExists();
        }
    }
    
    public function notExists(){
        http_response_code(404);
    }
}
