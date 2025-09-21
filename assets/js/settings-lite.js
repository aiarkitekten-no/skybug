(function($){
'use strict';
$(function(){
  var $smtpBtn = $('#test-smtp');
  var $imapBtn = $('#test-imap');
  var $smtpRes = $('#smtp-test-result');
  var $imapRes = $('#imap-test-result');
  function run(type,$btn,$res){
    var nonce = (skyBugSettings.noncles && skyBugSettings.noncles[type]) || (skyBugSettings.nonces && skyBugSettings.nonces[type]) || $btn.data('nonce');
    if(!nonce){
      if($res.length){ $res.text('Mangler nonce – last siden.').addClass('error').show(); }
      return;
    }
    var orig = $btn.text();
    $btn.addClass('loading').text(skyBugSettings.strings.testing||'Tester...');
    if($res.length){ $res.removeClass('success error').text('Tester forbindelse...').show(); }
    $.ajax({
      url: skyBugSettings.ajaxUrl,
      method:'POST',
      data:{ action:'skybug_test_'+type, nonce:nonce },
      dataType:'json',
      timeout:15000
    }).done(function(r){
      var ok = !!r.success;
      var cls = ok? 'success':'error';
      if($res.length){
        var html = (r.message||'');
        if(type==='imap' && r.attempts){
          html += '<details style="margin-top:8px"><summary>Forsøk detaljer</summary><table style="width:100%;margin-top:6px;font-size:12px;border-collapse:collapse"><thead><tr><th>Label</th><th>Mappe</th><th>Flags</th><th>OK</th><th>Meld.</th><th>Feil</th></tr></thead><tbody>';
          r.attempts.forEach(function(a){
            html += '<tr>'+
              '<td style="padding:2px 4px">'+(a.label||'')+'</td>'+
              '<td style="padding:2px 4px">'+(a.folder||'')+'</td>'+
              '<td style="padding:2px 4px">'+(a.flags||'')+'</td>'+
              '<td style="padding:2px 4px">'+(a.success? '✅':'❌')+'</td>'+
              '<td style="padding:2px 4px">'+(typeof a.messages!=='undefined'?a.messages:'')+'</td>'+
              '<td style="padding:2px 4px;color:#b00">'+(a.error||'')+'</td>'+
              '</tr>';
          });
          html += '</tbody></table></details>';
        }
        $res.html(html).removeClass('success error').addClass(cls).show();
      }
    }).fail(function(xhr){
      var msg='Feil ved test';
      try{ var jr = JSON.parse(xhr.responseText); if(jr.message) msg = jr.message; }catch(e){ msg = msg + ' ('+xhr.status+')'; }
      if($res.length){ $res.text(msg).removeClass('success').addClass('error').show(); }
    }).always(function(){
      $btn.removeClass('loading').text(orig);
    });
  }
  if($smtpBtn.length){ $smtpBtn.on('click', function(e){ e.preventDefault(); run('smtp',$smtpBtn,$smtpRes); }); }
  if($imapBtn.length){ $imapBtn.on('click', function(e){ e.preventDefault(); run('imap',$imapBtn,$imapRes); }); }
});
})(jQuery);
