(function($){
  if(typeof skybugProgramSingle==='undefined') return;
  const cfg = skybugProgramSingle;
  // Description sync
  const $d = $('#skybug-program-description');
  const $textarea = $('#skybug_program_content_hidden');
  function sync(){
    const html = $d.html().trim();
    $textarea.val(html);
  }
  let syncTimer = null;
  $d.on('input blur paste', function(){
    clearTimeout(syncTimer);
    syncTimer = setTimeout(sync, 300);
  });
  // toolbar buttons
  $(document).on('click','[data-format]', function(){
    const cmd = $(this).data('format');
    document.execCommand(cmd,false,null);
    sync();
  });
  // Reveal key
  $('#skybug-reveal-api-key').on('click', function(){
    const span = $('#skybug-api-key-value');
    const full = span.data('full');
    if(!full) return;
    const revealed = !!span.data('revealed');
    if(revealed){
      span.text(full.substring(0,4)+'\u2022\u2022\u2022\u2022'+full.substring(full.length-4)).data('revealed',false);
      $(this).text(cfg.showKey);
    } else {
      span.text(full).data('revealed',true);
      $(this).text(cfg.hideKey);
    }
  });
  // Regenerate
  $('#skybug-regenerate-api-key').on('click', function(){
    if(!confirm(cfg.confirmRegen)) return;
    const btn=$(this);
    btn.prop('disabled',true).text('…');
    $.post(ajaxurl,{action:'skybug_regenerate_program_key',nonce:cfg.nonce,program_id:cfg.programId}, function(r){
      if(r && r.success){
        const span=$('#skybug-api-key-value');
        span.text(r.data.masked).data('full', r.data.key).data('revealed',false);
        $('#skybug-program-feedback').css('color','#155724').text(cfg.regenOk);
        $('#skybug-reveal-api-key').text(cfg.showKey);
      } else {
        $('#skybug-program-feedback').css('color','#721c24').text(cfg.regenFail);
      }
      btn.prop('disabled',false).text(cfg.regenerate);
    });
  });
  // Test webhook
  $('#skybug-test-webhook').on('click', function(){
    const btn=$(this);
    btn.prop('disabled',true).text('…');
    $.post(ajaxurl,{action:'skybug_test_program_webhook',nonce:cfg.nonce,program_id:cfg.programId}, function(r){
      if(r && r.success){
        $('#skybug-program-feedback').css('color','#155724').text(cfg.testSent);
      } else {
        $('#skybug-program-feedback').css('color','#721c24').text(cfg.testFail);
      }
      btn.prop('disabled',false).text(cfg.testBtn);
    });
  });
  // Initialize sync
  sync();
})(jQuery);
