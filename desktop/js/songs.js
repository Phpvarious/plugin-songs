
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

$('#in_searchSong').off('keyup').keyup(function () {
  var search = $(this).value().toLowerCase();
  search = search.normalize('NFD').replace(/[\u0300-\u036f]/g, "")
  if(search == ''){
    $('.songDisplayCard').show();
    $('.eqLogicThumbnailContainer').packery();
    return;
  }
  $('.songDisplayCard').hide();
  $('.songDisplayCard .name').each(function(){
    var text = $(this).text().toLowerCase();
    text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, "")
    if(text.indexOf(search) >= 0){
      $(this).closest('.songDisplayCard').show();
    }
  });
  $('.eqLogicThumbnailContainer').packery();
});

 $('#bt_resetSearch').on('click', function() {
   $('#in_searchSong').val('').keyup()
 })

$('.songAction[data-action=add]').off('click').on('click',function(){
  bootbox.prompt("{{Nom du sons ?}}", function (result) {
    if (result !== null) {
      jeedom.songs.save({
        song : {name : result},
        error: function (error) {
          $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (_data) {
          var vars = getUrlVars();
          var url = 'index.php?';
          for (var i in vars) {
            if (i != 'id' && i != 'saveSuccessFull' && i != 'removeSuccessFull') {
              url += i + '=' + vars[i].replace('#', '') + '&';
            }
          }
          modifyWithoutSave = false;
          url += 'id=' + _data.id + '&saveSuccessFull=1';
          jeedomUtils.loadPage(url);
        }
      });
    }
  });
});

$('.songAction[data-action=gotoPluginConf]').on('click', function() {
  $('#md_modal').dialog({title: "{{Configuration du plugin}}"}).load('index.php?v=d&p=plugin&ajax=1&id='+eqType).dialog('open')
})

$('.songAction[data-action=returnToThumbnailDisplay]').removeAttr('href').off('click').on('click', function (event) {
  $('.song').hide();
  $('.songThumbnailDisplay').show();
  $('.eqLogicThumbnailContainer').packery();
});

var url = document.location.toString();
if (url.match('#')) {
  if(url.split('#')[1] == ''){
    $('.nav-tabs a:not(.eqLogicAction)').first().click();
  }else{
    $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').click();
  }
}else{
  $('.nav-tabs a:not(.eqLogicAction)').first().click();
}
$('.nav-tabs a').on('shown.bs.tab', function (e) {
  window.location.hash = e.target.hash;
})

$(".songDisplayCard").on('click', function () {
  if ($('.songThumbnailDisplay').html() != undefined) {
    $('.songThumbnailDisplay').hide();
  }
  $('.song').show();
  $('a[href="#songtab"]').closest('ul').find('li').removeClass('active');
  $('a[href="#songtab"]').closest('li').addClass('active');
  var _id = $(this).attr('data-song_id');
  try {
    $('#bt_uploadSong').fileupload('destroy');
    $('#bt_uploadSong').parent().html('<i class="fas fa-cloud-upload-alt"></i> {{Envoyer}}<input  id="bt_uploadSong" type="file" name="file" style="display: inline-block;">');
  } catch(error) {

  }
  $('#bt_uploadSong').fileupload({
    replaceFileInput: false,
    url: 'plugins/songs/core/ajax/songs.ajax.php?action=uploadSong&id=' +_id +'&jeedom_token='+JEEDOM_AJAX_TOKEN,
    dataType: 'json',
    done: function (e, data) {
      if (data.result.state != 'ok') {
        $('#div_alert').showAlert({message: data.result.result, level: 'danger'});
        return;
      }
      $('#div_alert').showAlert({message: '{{Son ajoutée}}', level: 'success'});
    }
  });
  jeedom.songs.get({
    id: _id,
    error: function (error) {
      $.hideLoading();
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {
      $('body .songAttr').value('');
      $('body').setValues(data, '.songAttr');
      $.hideLoading();
      jeedomUtils.addOrUpdateUrl('id',data.id);
      modifyWithoutSave = false;
    }
  });
});

$('.songAction[data-action=remove]').on('click', function () {
  bootbox.confirm('{{Etês vous sur de vouloir supprimer ce son ?}}', function (result) {
    if (result) {
      jeedom.songs.remove({
        id: $('.songAttr[data-l1key=id]').value(),
        error: function (error) {
          $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function () {
          var vars = getUrlVars();
          var url = 'index.php?';
          for (var i in vars) {
            if (i != 'id' && i != 'removeSuccessFull' && i != 'saveSuccessFull') {
              url += i + '=' + vars[i].replace('#', '') + '&';
            }
          }
          modifyWithoutSave = false;
          url += 'removeSuccessFull=1';
          jeedomUtils.loadPage(url);
        }
      });
    }
  });
});

$('.songAction[data-action=save]').on('click', function () {
  jeedom.songs.save({
    song : $('.song').getValues('.songAttr')[0],
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {
      modifyWithoutSave = false;
      var vars = getUrlVars();
      var url = 'index.php?';
      for (var i in vars) {
        if (i != 'id' && i != 'saveSuccessFull' && i != 'removeSuccessFull') {
          url += i + '=' + vars[i].replace('#', '') + '&';
        }
      }
      url += 'id=' + data.id + '&saveSuccessFull=1';
      if (document.location.toString().match('#')) {
        url += '#' + document.location.toString().split('#')[1];
      }
      jeedomUtils.loadPage(url);
      modifyWithoutSave = false;
    }
  });
});

if (is_numeric(getUrlVars('id'))) {
  if ($('.eqLogicThumbnailContainer .songDisplayCard[data-song_id=' + getUrlVars('id') + ']').length != 0) {
    $('.eqLogicThumbnailContainer .songDisplayCard[data-song_id=' + getUrlVars('id') + ']').click();
  }
}
