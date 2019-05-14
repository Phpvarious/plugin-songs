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

try {
  require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
  include_file('core', 'authentification', 'php');
  
  if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
  }
  
  ajax::init();
  
  if (init('action') == 'save') {
    unautorizedInDemo();
    $song_ajax = json_decode(init('song'), true);
    $song = null;
    if(isset($song_ajax['id'])){
      $song = songs_song::byId($song_ajax['id']);
    }
    if(!is_object($song)){
      $song = new songs_song();
    }
    utils::a2o($song, $song_ajax);
    $song->save();
    ajax::success(utils::o2a($song));
  }
  
  if (init('action') == 'get') {
    unautorizedInDemo();
    $song = songs_song::byId(init('id'));
    if (!is_object($song)) {
      throw new Exception(__('Son inconnu : ', __FILE__) . init('id'), 9999);
    }
    ajax::success(utils::o2a($song));
  }
  
  if (init('action') == 'remove') {
    unautorizedInDemo();
    $song = songs_song::byId(init('id'));
    if (!is_object($song)) {
      throw new Exception(__('Son inconnu : ', __FILE__) . init('id'), 9999);
    }
    $song->remove();
    ajax::success();
  }
  
  if (init('action') == 'uploadSong') {
    if (!isConnect('admin')) {
      throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }
    unautorizedInDemo();
    $song = songs_song::byId(init('id'));
    if (!is_object($song)) {
      throw new Exception(__('Son inconnu. Vérifiez l\'ID', __FILE__));
    }
    if (!isset($_FILES['file'])) {
      throw new Exception(__('Aucun fichier trouvé. Vérifiez le paramètre PHP (post size limit)', __FILE__));
    }
    $extension = strtolower(strrchr($_FILES['file']['name'], '.'));
    if (!in_array($extension, array('.mp3'))) {
      throw new Exception('Extension du fichier non valide (autorisé .mp3) : ' . $extension);
    }
    if (filesize($_FILES['file']['tmp_name']) > 50000000) {
      throw new Exception(__('Le fichier est trop gros (maximum 50Mo)', __FILE__));
    }
    if(!file_exists(__DIR__ . '/../../data')){
      mkdir(__DIR__ . '/../../data');
    }
    $files = ls(__DIR__ . '/../../data/','song'.$song->getId().'*');
    if(count($files)  > 0){
      foreach ($files as $file) {
        unlink(__DIR__ . '/../../data/'.$file);
      }
    }
    $song->setOptions('type', str_replace('.', '', $extension));
    $song->setOptions('sha512', sha512(file_get_contents($_FILES['file']['tmp_name'])));
    $filename = 'song'.$song->getId().'-'.$song->getOptions('sha512') . '.' . $song->getOptions('type');
    $filepath = __DIR__ . '/../../data/' . $filename;
    file_put_contents($filepath,file_get_contents($_FILES['file']['tmp_name']));
    if(!file_exists($filepath)){
      throw new \Exception(__('Impossible de sauvegarder le son',__FILE__));
    }
    $song->setPath($filepath);
    $song->save();
    ajax::success(array('filepath' => $filepath));
  }
  
  
  throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
  /*     * *********Catch exeption*************** */
} catch (Exception $e) {
  ajax::error(displayException($e), $e->getCode());
}
