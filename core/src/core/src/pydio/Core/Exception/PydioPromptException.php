<?php
/*
 * Copyright 2007-2016 Charles du Jeu - Abstrium SAS <team (at) pyd.io>
 * This file is part of Pydio.
 *
 * Pydio is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Pydio is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Pydio.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <https://pydio.com>.
 */
namespace Pydio\Core\Exception;

use Pydio\Core\Http\Response\JSONSerializableResponseChunk;
use Pydio\Core\Http\Response\XMLSerializableResponseChunk;

defined('AJXP_EXEC') or die( 'Access not allowed');

define('AJXP_PROMPT_EXCEPTION_PROMPT', 'AJXP_PROMPT_EXCEPTION_PROMPT');
define('AJXP_PROMPT_EXCEPTION_CONFIRM', 'AJXP_PROMPT_EXCEPTION_CONFIRM');
define('AJXP_PROMPT_EXCEPTION_ALERT', 'AJXP_PROMPT_EXCEPTION_ALERT');

/**
 * Class AJXP_PromptException
 * Specific exception that triggers a prompt in the UI instead of displaying an error message.
 *
 * @package Pydio
 * @subpackage Core
 */
class PydioPromptException extends PydioException implements XMLSerializableResponseChunk, JSONSerializableResponseChunk
{

    private $promptType = "prompt";
    /**
     * @var array
     */
    private $promptData = array();

    /**
     * @return array
     */
    public function getPromptData()
    {
        return $this->promptData;
    }

    /**
     * @return string
     */
    public function getPromptType()
    {
        return $this->promptType;
    }

    /**
     * @param $promptType
     * @param array $data
     * @param String $messageString
     * @param string|bool $messageId
     */
    public function __construct($promptType, $data, $messageString, $messageId = false)
    {
        $this->promptType = $promptType;
        $this->promptData = $data;
        parent::__construct($messageString, $messageId);
    }

    /**
     * Prompt user for credentials
     * @param $sessionVariable
     * @param $switchToRepositoryId
     * @throws PydioPromptException
     */
    public static function testOrPromptForCredentials($sessionVariable, $switchToRepositoryId){
        if(isSet($_GET["prompt_passed_data"]) && isSet($_GET["variable_name"]) && $_GET["variable_name"] == $sessionVariable){
            $_SESSION[$sessionVariable] = true;
        }
        if(!isSet($_SESSION[$sessionVariable])){
            throw new PydioPromptException(
                "confirm",
                array(
                    "DIALOG" => "Please enter your credentials for this workspace
                                <input type='hidden' name='get_action' value='switch_repository'>
                                <input type='hidden' name='repository_id' value='".$switchToRepositoryId."'>
                                <input type='hidden' name='prompt_passed_data' value='true'>
                                <input type='hidden' name='variable_name' value='".$sessionVariable."'>
                                ",
                    "OK"        => array(
                        "GET_FIELDS" => array("get_action", "repository_id", "prompt_passed_data", "variable_name"),
                        "EVAL" => "ajaxplorer.loadXmlRegistry();"
                    ),
                    "CANCEL"    => array(
                        "EVAL" => "ajaxplorer.loadXmlRegistry();"
                    )
                ),
                "Credentials Needed");
        }

    }

    /**
     * @return mixed
     */
    public function jsonSerializableData()
    {
        return [
            "promptType" => $this->promptType,
            "promptMessage" => $this->getMessage(),
            "promptData" => $this->promptData
        ];
    }

    /**
     * @return string
     */
    public function jsonSerializableKey()
    {
        return "userPrompt";
    }

    /**
     * @return string
     */
    public function toXML()
    {
        return "<prompt type=\"".$this->promptType."\"><message>".$this->getMessage()."</message><data><![CDATA[".json_encode($this->promptData)."]]></data></prompt>";
    }


}