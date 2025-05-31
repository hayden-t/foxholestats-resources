<?php
/**
 * This code is free software; you can redistribute it and/or modify it under
 * the terms of the new BSD License.
 *
 * Copyright (c) 2008-2011, Sebastian Staudt
 *
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

require_once STEAM_CONDENSER_PATH . 'steam/SteamPlayer.php';
require_once STEAM_CONDENSER_PATH . 'steam/packets/SteamPacket.php';

/**
 * This class represents a S2A_PLAYER response sent by a game server
 *
 * It is used to transfer a list of players currently playing on the server.
 *
 * @author     Sebastian Staudt
 * @package    steam-condenser
 * @subpackage packets
 * @see        GameServer::updatePlayerInfo()
 */
class S2A_PLAYER_Packet extends SteamPacket {

    /**
     * @var array
     */
    private $playerHash;

    /**
     * Creates a new S2A_PLAYER response object based on the given data
     *
     * @param string $contentData The raw packet data sent by the server
     * @throws PacketFormatException if the packet data is missing
     */
    public function __construct($contentData) {

        if (empty($contentData)) {
            throw new PacketFormatException('Wrong formatted S2A_RULES packet.');
        }
        parent::__construct(SteamPacket::S2A_PLAYER_HEADER, $contentData);


        $this->playerHash = array();
       echo "<pre>";

        $hexStream = bin2hex($this->contentData->_array());

        $hexStream = str_split($hexStream, 2);		

		$temp = array();
		$count = 0;
		$playersCount = 0;
		$totalPlayers = array_shift($hexStream);
		array_shift($hexStream);//blank
		$skip = 0;
        foreach($hexStream as $key => $byte){
			if($key < $skip)continue;
			
			if($byte=='00')$count++;
			else $count = 0;
			
			$temp[] = $byte;
			
			if($count == 5){

				$name = array_slice($temp,0,-5);//remove '00's

				$name = hex2bin(implode($name));
					
				$time = array_slice($hexStream,$key+1,4);//4 byte 'score'
				$time = implode($time);
				$time = hex2bin($time);
				$time = unpack('f', $time)[1];
				
				$skip = $key+6;//skip 6 places

				$temp = array($name, $time);
				
				$this->playerHash[$playersCount++] = new SteamPlayer($playersCount, $temp[0], 0, $temp[1]);
				
				$count = 0;
				$temp=array();
			}
        }
       //  var_dump($playerArray);
  
		echo "</pre>";

/*        while($this->contentData->remaining() > 0) {	
            $playerData = array($this->contentData->getByte(), $this->contentData->getString(), $this->contentData->getLong(), $this->contentData->getFloat());
            $this->playerHash[$playerData[1]] = new SteamPlayer($playerData[0], $playerData[1], $playerData[2], $playerData[3]);
        }*/

         
      
        
    }

    /**
     * Returns the list of active players provided by the server
     *
     * @return array All active players on the server
     */
    public function getPlayerHash() {
        return $this->playerHash;
    }
}
