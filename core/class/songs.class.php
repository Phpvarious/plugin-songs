<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class songs extends eqLogic {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  public static function tts($_filename,$_text) {
    if (substr($_text, -1) == '#' && substr($_text, 0, 1) == '#') {
      log::add('tts', 'debug', __('Tag detécté dans le tts', __FILE__));
      $song = songs_song::byLogicalId(strtolower(str_replace('#', '', init('text'))));
      if (is_object($song) && file_exists($song->getPath())) {
        log::add('tts', 'debug', __('Son trouvé path :', __FILE__) . ' ' . $song->getPath());
        copy($song->getPath(),$_filename);
        return;
      }
    }
    try {
      $url = config::byKey('service::cloud::url').'/user/';
      $url .= sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'));
      $url .= '/service/tts';
      $url .= '?lang='.config::byKey('language', 'core', 'fr_FR');
      $url .= '&text='.urlencode($_text);
      $request_http = new com_http(trim($url,'&'));
      $datas = $request_http->exec();
      if(is_json($datas)){
        throw new \Exception(__('Erreur sur la récuperation des données : ',__FILE__).$datas);
      }
      file_put_contents($_filename, $datas);
    } catch (Exception $e) {
      log::add('dataservice', 'error', '[TTS] ' . $e->getMessage());
    }
  }
  
  
  /*     * *********************Méthodes d'instance************************* */
  
  
  
  /*     * **********************Getteur Setteur*************************** */
}

class songsCmd extends cmd {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  
  /*     * *********************Methode d'instance************************* */
  
  
  /*     * **********************Getteur Setteur*************************** */
}

class songs_song{
  
  /*     * *************************Attributs****************************** */
  
  private $id;
  private $name;
  private $logicalId;
  private $path;
  private $options;
  private $_changed;
  
  
  /*     * ***********************Methode static*************************** */
  
  public static function all(){
    $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
    FROM songs_song';
    return DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
  }
  
  public static function byId($_id) {
    $values = array(
      'id' => $_id,
    );
    $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
    FROM songs_song
    WHERE id=:id';
    return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
  }
  
  public static function byLogicalId($_logicalId) {
    $values = array(
      'logicalId' => $_logicalId,
    );
    $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
    FROM songs_song
    WHERE logicalId=:logicalId';
    return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
  }
  
  /*     * *********************Methode d'instance************************* */
  
  public function preSave() {
    if($this->getName() == ''){
      throw new \Exception(__('Le nom ne peut etre vide',__FILE__));
    }
    if(is_numeric($this->getName())){
      throw new \Exception(__('Le nom ne peut etre un nombre',__FILE__));
    }
    if($this->getLogicalId() == ''){
      $this->setLogicalId($this->getName());
    }
    $this->setLogicalId(strtolower($this->getLogicalId()));
  }
  
  public function save() {
    return DB::save($this);
  }
  
  
  public function remove() {
    if(file_exists($this->getPath())){
      unlink($this->getPath());
    }
    DB::remove($this);
  }
  /*     * **********************Getteur Setteur*************************** */
  
  public function getId() {
    return $this->id;
  }
  
  public function setId($_id) {
    $this->_changed = utils::attrChanged($this->_changed,$this->id,$_id);
    $this->id = $_id;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function setName($_name) {
    $_name = cleanComponanteName($_name);
    $this->_changed = utils::attrChanged($this->_changed,$this->name,$_name);
    $this->name = $_name;
  }
  
  public function getLogicalId() {
    return $this->logicalId;
  }
  
  public function setLogicalId($_logicalId) {
    $_logicalId = cleanComponanteName($_logicalId);
    $this->_changed = utils::attrChanged($this->_changed,$this->logicalId,$_logicalId);
    $this->logicalId = $_logicalId;
  }
  
  public function getPath() {
    return $this->path;
  }
  
  public function setPath($_path) {
    $this->_changed = utils::attrChanged($this->_changed,$this->path,$_path);
    $this->path = $_path;
  }
  
  public function getOptions($_key = '', $_default = '') {
    return utils::getJsonAttr($this->options, $_key, $_default);
  }
  
  public function setOptions($_key, $_value) {
    $options = utils::setJsonAttr($this->options, $_key, $_value);
    $this->_changed = utils::attrChanged($this->_changed,$this->options,$options);
    $this->options = $options;
  }
  
  public function getCache($_key = '', $_default = '') {
    if ($this->_cache == null) {
      $this->_cache = cache::byKey('songCache' . $this->getId())->getValue();
    }
    return utils::getJsonAttr($this->_cache, $_key, $_default);
  }
  
  public function setCache($_key, $_value = null) {
    $this->_cache = utils::setJsonAttr(cache::byKey('songCache' . $this->getId())->getValue(), $_key, $_value);
    cache::set('songCache' . $this->getId(), $this->_cache);
  }
  
  public function getChanged() {
    return $this->_changed;
  }
  
  public function setChanged($_changed) {
    $this->_changed = $_changed;
    return $this;
  }
  
}
