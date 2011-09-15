<?php
/**
 *
 * Copyright 2005-2006 The Apache Software Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/* vim: set expandtab tabstop=3 shiftwidth=3: */
if (!class_exists('Services_JSON')) {
    require_once('JSON.php');
}

/**
 * StompFrames are messages that are sent and received on a StompConnection.
 *
 * @package Stomp
 * @author Hiram Chirino <hiram@hiramchirino.com>
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision: 7479 $
 */ 
class StompFrame {
    var $command;
    var $headers = array();
    var $body;
    
    function StompFrame($command = null, $headers=null, $body=null) {
        $this->init($command, $headers, $body);
    }
    
    function init($command = null, $headers=null, $body=null) {
        $this->command = $command;
        if ($headers != null)
          $this->headers = $headers;
        $this->body = $body;
    }
}

/**
 * Basic text stomp message
 *
 * @package Stomp
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision: 7479 $
 */  
class StompMessage extends StompFrame {
    
    function StompMessage($body, $headers = null) {
        $this->init("SEND", $headers, $body);
    }
    
}

/**
 * Message that contains a stream of uninterpreted bytes
 *
 * @package Stomp
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision: 7479 $
 */  
class BytesMessage extends StompMessage {
    function ByteMessage($body, $headers = null) {
        $this->init("SEND", $headers, $body);
        if ($this->headers == null) {
          $this->headers = array();
        }
        $this->headers['content-length'] = count($body);
    }
}

/**
 * Message that contains a set of name-value pairs
 *
 * @package Stomp
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision: 7479 $
 */
class MapMessage extends StompMessage {
    
    var $map;
    
    function MapMessage($msg, $headers = null) {
        if (is_a($msg, "StompFrame")) {
            $this->init($msg->command, $msg->headers, $msg->body);
            $json = new Services_JSON();
            $this->map = $json->decode($msg->body);
        } else {
          $this->init("SEND", $headers, $msg);
          if ($this->headers == null) {
            $this->headers = array();
          }
          $this->headers['amq-msg-type'] = 'MapMessage';
          $json = new Services_JSON();
          $this->body = $json->encode($msg);
        }
    }
    
}

/**
 * A Stomp Connection
 *
 *
 * @package Stomp
 * @author Hiram Chirino <hiram@hiramchirino.com>
 * @author Dejan Bosanac <dejan@nighttale.net> 
 * @version $Revision: 7479 $
 */
class StompConnection {

    var $socket = null;
    var $hosts = array();
    var $params = array();
    var $subscriptions = array();
    var $defaultPort = 61613;
    var $currentHost = -1;
    var $attempts = 10;
    var $username = '';
    var $password = '';

    function StompConnection($brokerUri) {
		$ereg = "/^(([\w]+):\/\/)+\(*([\w\d\.:\/i,-]+)\)*\??([\w\d=]*)$/i";
        if (preg_match($ereg, $brokerUri, $regs)) {
          $scheme = $regs[2];
          $hosts = $regs[3];
          $params = $regs[4];
          if ($scheme != "failover") {
              $this->processUrl($brokerUri);
          } else {
              $urls = explode(",", $hosts);
              foreach($urls as $url) {
                  $this->processUrl($url);
              }
          }
          
          if ($params != null) {
            parse_str($params, $this->params);
          }
          
          $this->makeConnection();
          
        } else {
          //trigger_error("Bad Broker URL $brokerUri", E_USER_ERROR);
        }
    }
    
    function processUrl($url) {
       $parsed = parse_url($url);
       if ($parsed) {
         $scheme = $parsed['scheme'];
         $host = $parsed['host'];
         $port = $parsed['port'];
         array_push($this->hosts, array($parsed['host'], $parsed['port'], $parsed['scheme']));
       } else {
         //trigger_error("Bad Broker URL $url", E_USER_ERROR);
       }
    }
    
    function makeConnection() {
      if (count($this->hosts) == 0) {
        //trigger_error("No broker defined", E_USER_ERROR);
      }
      
      $i = $this->currentHost;
      $att = 0;
      $connected = false;
      
      while (!$connected && $att++ < $this->attempts) {
        if (@$this->params['randomize'] != null 
            && $this->params['randomize'] == 'true') {
          $i = rand(0, count($this->hosts) - 1);  
        } else {
          $i = ($i + 1) % count($this->hosts);
        }
              
        $broker = $this->hosts[$i];

        $host = $broker[0];
        $port = $broker[1];
        $scheme = $broker[2];
        ////trigger_error("connecting to: $scheme://$host:$port");
        if ($port == null) {
          $port = $this->defaultPort;
        }
        
        if ($this->socket != null) {
          //trigger_error("Closing existing socket");
          fclose($this->socket);
          $this->socket = null;
        } 
        
        $this->socket = @fsockopen($scheme.'://'.$host, $port, $errorNum, $errorString, 2);
 
        if ($this->socket == null) {
			////trigger_error("Could not connect to $host:$port ({$att}/{$this->attempts})", E_USER_WARNING);
        } else {
          //trigger_error("Connected");
          $connected = true;
          $this->currentHost = $i;
          break;
        }
        
      }
      
      if (!$connected) {
        ////trigger_error("Could not connect to a broker", E_USER_ERROR);
        throw new Exception("Could not connect to a broker");
      }
      
    }

    function connect($username="", $password="") {
        if ($username != "")
          $this->username = $username;

        if ($password != "")
          $this->password = $password;          
          
        $this->writeFrame( new StompFrame("CONNECT", array("login"=>$this->username, "passcode"=> $this->password ) ) );        
        return $this->readFrame();
    }

    function send($destination, $msg, $properties=null) {
        if (is_a($msg, 'StompFrame')) {
          $msg->headers["destination"] = $destination;
          $this->writeFrame($msg);
        } else {
          //trigger_error("sending '$msg' message to '$destination'");        
          $headers = array();
          if( isset($properties) ) {
            foreach ($properties as $name => $value) {
                $headers[$name] = $value;
            }
          }
          $headers["destination"] = $destination ;
          $this->writeFrame( new StompFrame("SEND", $headers, $msg) );
          //trigger_error("'$msg' message sent to '$destination'");
        }
    }
    
    function subscribe($destination, $properties=null) {
        $headers = array("ack"=>"client");
        if( isset($properties) ) {
            foreach ($properties as $name => $value) {
                $headers[$name] = $value;
            }
        }
        $headers["destination"] = $destination ;
        $this->writeFrame( new StompFrame("SUBSCRIBE", $headers) );
        $this->subscriptions[$destination] = $properties;
    }
    
    function unsubscribe($destination, $properties=null) {
        $headers = array();
        if( isset($properties) ) {
            foreach ($properties as $name => $value) {
                $headers[$name] = $value;
            }
        }
        $headers["destination"] = $destination ;
        $this->writeFrame( new StompFrame("UNSUBSCRIBE", $headers) );
        unset($this->subscriptions[$destination]);
    }

    function begin($transactionId=null) {
        $headers = array();
        if( isset($transactionId) ) {
            $headers["transaction"] = $transactionId;
        }
        $this->writeFrame( new StompFrame("BEGIN", $headers) );
    }
    
    function commit($transactionId=null) {
        $headers = array();
        if( isset($transactionId) ) {
            $headers["transaction"] = $transactionId;
        }
        $this->writeFrame( new StompFrame("COMMIT", $headers) );
    }

    function abort($transactionId=null) {
        $headers = array();
        if( isset($transactionId) ) {
            $headers["transaction"] = $transactionId;
        }
        $this->writeFrame( new StompFrame("ABORT", $headers) );
    }
    
    function ack($message, $transactionId=null) {
        if (is_a($message, 'StompFrame')) {
          $this->writeFrame( new StompFrame("ACK", $message->headers) );
        } else {
          $headers = array();
          if( isset($transactionId) ) {
            $headers["transaction"] = $transactionId;
          }
          $headers["message-id"] = $message ;
          $this->writeFrame( new StompFrame("ACK", $headers) );
        }
    }
    
    function disconnect() {
        $this->writeFrame( new StompFrame("DISCONNECT") );
        fclose($this->socket);
    }
    
    function writeFrame($stompFrame) {
        //trigger_error($stompFrame->command);
        $data = $stompFrame->command . "\n";        
        if( isset($stompFrame->headers) ) {
            foreach ($stompFrame->headers as $name => $value) {
                $data .= $name . ": " . $value . "\n";
            }
        }
        $data .= "\n";
        if( isset($stompFrame->body) ) {
            $data .= $stompFrame->body;
        }
        $l1 = strlen($data);
        $data .= "\x00\n";
        $l2 = strlen($data);
        
        $noop = "\x00\n";
        fwrite($this->socket, $noop, strlen($noop));
        
        $r = fwrite($this->socket, $data, strlen($data));
        if ($r === false || $r == 0) {
          //trigger_error("Could not send stomp frame to server");
          $this->reconnect();

          $this->writeFrame($stompFrame);
        }

    }
    
    function readFrame() {
      
        $rc = fread($this->socket, 1);
        
        if($rc === false) {
            $this->reconnect();
            return $this->readFrame();
        }
            
        $data = $rc;
        $prev = '';
        // Read until end of frame.
        while (!feof($this->socket)) {
          $rc = fread($this->socket, 1);
          
          if ($rc === false) {
            $this->reconnect();
            return $this->readFrame();
          }
          
          $data .= $rc;
          
          if(ord($rc) == 10 && ord($prev) == 0) {
            break;
          }
          $prev = $rc;
        }
        
        list($header, $body) = explode("\n\n", $data, 2);
        $header = explode("\n", $header);
        $headers = array();
        
        $command = null;
        foreach ($header as $v) {
           if( isset($command) ) {
                list($name, $value) = explode(':', $v, 2);
                $headers[$name]=$value;
           } else {
                $command = $v;
           }
        }
        
         $frame = new StompFrame($command, $headers, trim($body));
         if (isset($frame->headers['amq-msg-type']) 
              && $frame->headers['amq-msg-type'] == 'MapMessage') {
          return new MapMessage($frame);
         } else {
          return $frame;
         }
    }
    
    /**
     * Reconnects and renews subscriptions (if there were any)
     * Call this method when you detect connection problems     
     */
    function reconnect() {
      $this->makeConnection();
      $this->connect();
      foreach($this->subscriptions as $dest=>$properties) {
        $this->subscribe($dest, $properties);
      }
    }
}



