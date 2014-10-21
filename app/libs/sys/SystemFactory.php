<?php
class SysFactory
{
    function __set ($name, $value)
    {
        
       if( is_numeric($value))
       {
//           snk::db('xx',"$name = {$name}{$value}");
       }else {
//          snk::db('xx',"{$name}={$value}") ;
       }
//       $this->$name = $value;
       switch( $name )
       {
        case 'status':
//            role.status = role.status +1;
            break;
       }
    }
    function __get ($class_name)
    {
        
    }
    function __call( $name, $arguments )
    {
        
        
        
    }
    
    
}
