(function($){
    const app = $('#skybug-single-app');
    if(!app.length) return;
    const issueId = app.data('issue-id');
    const prioritySel = $('#skybug_priority_inline');
    const typeSel = $('#skybug_type_inline');
    const statusBtns = $('.skybug-status-btn');
    const commentsWrap = $('#skybug-internal-comments');
    const addBtn = $('#skybug-add-internal');
    const textarea = $('#skybug-new-internal');

    // Init selects to current values
    prioritySel.val(app.data('current-priority'));
    typeSel.val(app.data('current-type'));
    statusBtns.each(function(){ if($(this).data('status')===app.data('current-status')) $(this).addClass('active'); });

    function api(action,data){
        return $.post(skybugIssueModern.ajaxUrl, Object.assign({action:action}, data));
    }

    // Status change
    statusBtns.on('click', function(){
        const btn = $(this);
        const status = btn.data('status');
        statusBtns.removeClass('active');
        btn.addClass('active');
        const original = btn.text();
        btn.prop('disabled', true).text('…');
        $.post(skybugIssueModern.ajaxUrl,{action:'skybug_quick_status',post_id:issueId,status:status,nonce:skybugIssueModern.nonce}, null, 'json')
            .done(o=>{ if(o && o.success){
                    app.attr('data-current-status', status);
                    // Synk select i submit boksen slik at save hook bruker riktig verdi
                    $('#skybug_issue_status').val(status);
                } else { btn.removeClass('active'); alert('Status ikke lagret'); console.warn('Status response', o); } })
            .fail(()=>{ btn.removeClass('active'); alert('Feil ved statuslagring'); })
            .always(()=>btn.prop('disabled', false).text(original));
    });

    // Internal comment add
    addBtn.on('click', function(){
        const val = textarea.val().trim();
        if(!val) return;
        addBtn.prop('disabled', true).text(skybugIssueModern.i18n.saving);
        api('skybug_add_internal_comment',{post_id:issueId,comment:val,nonce:skybugIssueModern.commentsNonce})
            .done(r=>{ try{var o=JSON.parse(r);}catch(e){return;} if(o.success){ commentsWrap.append(o.html); textarea.val(''); } })
            .always(()=>addBtn.prop('disabled', false).text(skybugIssueModern.i18n.added));
    });

    // Priority & Type change -> set hidden inputs or create if missing so WordPress save hook still works
    function ensureHidden(name){ let el = $('input[name="'+name+'"][type=hidden]'); if(!el.length){ el = $('<input type="hidden" name="'+name+'" />').appendTo('#post'); } return el; }
    const hPriority = ensureHidden('_skybug_priority');
    const hType = ensureHidden('skybug_type');

    function reflect(){ hPriority.val(prioritySel.val()); hType.val(typeSel.val()); }
    prioritySel.on('change', reflect); typeSel.on('change', reflect); reflect();

    // Prevent accidental submit on Enter inside textarea
    textarea.on('keydown', e=>{ if(e.key==='Enter' && e.ctrlKey){ addBtn.click(); } });

    // Inline rich text editor
    const rich = $('#skybug-rich-editor');
    if(rich.length){
        // Ensure hidden original content textarea (#content) exists and keep synced
        const wpContent = $('#content');
        function syncToHidden(){ if(wpContent.length){ wpContent.val(rich.html()); } }
        // Debounce sync
        let syncTimer; function scheduleSync(){ clearTimeout(syncTimer); syncTimer = setTimeout(syncToHidden, 300); }
        rich.on('input blur paste', scheduleSync);
        // Toolbar actions
        $('.skybug-editor-toolbar button').on('click', function(){
            const cmd = $(this).data('command');
            if(cmd==='createLink'){
                const url = prompt('URL');
                if(url){ document.execCommand('createLink', false, url); // Force target=_blank afterwards
                    rich.find('a').attr('target','_blank').attr('rel','noopener');
                }
            } else if(cmd){
                document.execCommand(cmd, false, null);
            }
            scheduleSync();
        });
        // Ctrl+Enter triggers form submit (save post)
        rich.on('keydown', function(e){
            if(e.ctrlKey && e.key==='Enter'){
                e.preventDefault();
                syncToHidden();
                $('#publish').click();
            }
        });
    }

    // Email send panel
    const emailBtn = $('#skybug-email-send');
    if(emailBtn.length){
        emailBtn.on('click', function(){
            const subject = $('#skybug-email-subject').val().trim();
            const message = $('#skybug-email-message').val().trim();
            const feedback = $('.skybug-email-feedback');
            if(!subject || !message) return;
            emailBtn.prop('disabled', true).text(skybugIssueModern.i18n.sending);
            $.post(skybugIssueModern.ajaxUrl, {action:'skybug_send_email',nonce:skybugIssueModern.emailNonce,post_id:issueId,subject:subject,message:message})
                .done(r=>{ let o; try{o=JSON.parse(r);}catch(e){} if(o && o.success){
                    feedback.text(skybugIssueModern.i18n.sent).css({color:'green'}).show();
                    $('#skybug-email-message').val('');
                } else { feedback.text(skybugIssueModern.i18n.error).css({color:'red'}).show(); } })
                .fail(()=>feedback.text(skybugIssueModern.i18n.error).css({color:'red'}).show())
                .always(()=>emailBtn.prop('disabled', false).text('Send'));
        });
    }
})(jQuery);