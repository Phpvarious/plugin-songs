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


jeedom.songs = function() {};


jeedom.songs.save = function(_params){
  var paramsRequired = ['song'];
  var paramsSpecifics = {};
  try {
    jeedom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = jeedom.private.getParamsAJAX(params);
  paramsAJAX.async =  _params.async || true;
  
  paramsAJAX.url = 'plugins/songs/core/ajax/songs.ajax.php';
  paramsAJAX.data = {
    action: 'save',
    song: json_encode(_params.song)
  };
  $.ajax(paramsAJAX);
};


jeedom.songs.get = function(_params){
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  try {
    jeedom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = jeedom.private.getParamsAJAX(params);
  paramsAJAX.async =  _params.async || true;
  paramsAJAX.url = 'plugins/songs/core/ajax/songs.ajax.php';
  paramsAJAX.data = {
    action: 'get',
    id: _params.id
  };
  $.ajax(paramsAJAX);
}

jeedom.songs.remove = function(_params){
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  try {
    jeedom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = jeedom.private.getParamsAJAX(params);
  paramsAJAX.async =  _params.async || true;
  paramsAJAX.url = 'plugins/songs/core/ajax/songs.ajax.php';
  paramsAJAX.data = {
    action: 'remove',
    id: _params.id
  };
  $.ajax(paramsAJAX);
}
